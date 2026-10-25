<?php

namespace Modules\BackOffice\Services;

use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Modules\BackOffice\Emails\InspectionSendEmail;
use Modules\BackOffice\Entities\Pdc;
use Modules\BackOffice\Entities\Termination;
use Modules\BackOffice\Entities\TerminationChecklist;
use Modules\BackOffice\Entities\TerminationDocument;
use Modules\Maintenance\Exceptions\InvalidStateException;
use Modules\Maintenance\Exceptions\NotAssignedException;
use Modules\Masters\Entities\SubWork;
use Modules\Masters\Entities\Work;
use Modules\Masters\Services\AreBuildings;
use Modules\MobileApi\Services\MobileImage;
use Modules\Sales\Entities\TenantContract;

/**
 * The move-out (handover) inspection of a terminating contract, as the desktop
 * does it on the Handover Assigned screen: a 503 "inspection" row that carries
 * the readings and totals, termination_checklists lines, termination_document
 * photos, then the tenant signature and ACPT to Taken Over (504).
 *
 * Totals use the desktop's arithmetic:
 *   total            = Σ line.amount
 *   elec_water_total = electricity.amount + water.amount
 *   net              = total + elec_water_total − maintenance_due
 */
class TerminationInspection
{
    public const PHOTO_TYPES = [
        'inspection' => 'Inspection',
        'meter_electricity' => 'Electricity',
        'meter_water' => 'Water',
        'key' => 'Key',
    ];

    // ---- queue --------------------------------------------------------------

    /** The latest termination row per contract (type 1), in the ARE's buildings. */
    private static function latestInScope(User $are, ?int $buildingId): Builder
    {
        $buildings = AreBuildings::forUser($are);
        if ($buildingId !== null) {
            $buildings = $buildings->intersect([$buildingId]);
        }
        return Termination::query()
            ->with(['tenantContract.building', 'tenantContract.unit', 'tenantContract.tenant'])
            ->where('termination_type', 1)
            ->whereNotExists(fn ($q) => $q->selectRaw('1')->from('termination as later')
                ->whereColumn('later.contract_id', 'termination.contract_id')
                ->where('later.termination_type', 1)
                ->whereColumn('later.id', '>', 'termination.id'))
            ->whereHas('tenantContract', fn ($c) => $c->whereIn('building_id', $buildings->all())->where('tenant_contract_status', 1))
            ->orderBy('termination_date')->orderByDesc('id');
    }

    /**
     * pending: 502, or 503 assigned to me with nothing saved yet (or referred back and not yet re-saved by me)
     * inspected: 503 assigned to me with a saved draft
     * taken_over: 504 submitted by me in the last 90 days
     */
    public static function queue(User $are, string $tab, ?int $buildingId = null): Builder
    {
        $q = self::latestInScope($are, $buildingId);
        $referredBack = fn ($w) => $w->where('is_resubmit', 2)->where(fn ($u) => $u->whereNull('updated_by')->orWhere('updated_by', '!=', $are->id));
        return match ($tab) {
            'pending' => $q->where(fn ($w) => $w
                ->where('work_flow_processes_code', TerminationWorkflow::HANDOVER_UNASSIGNED)
                ->orWhere(fn ($m) => $m->where('work_flow_processes_code', TerminationWorkflow::HANDOVER_ASSIGNED)->where('assigned_to', $are->id)
                    ->where(fn ($n) => $n->whereNull('termination_notes')->orWhere($referredBack)))),
            'inspected' => $q->where('work_flow_processes_code', TerminationWorkflow::HANDOVER_ASSIGNED)->where('assigned_to', $are->id)
                ->whereNotNull('termination_notes')->whereNot($referredBack),
            'taken_over' => $q->where('work_flow_processes_code', TerminationWorkflow::TAKEN_OVER)->where('created_by', $are->id)
                ->where('created_at', '>=', now()->subDays(90)),
            default => throw new \InvalidArgumentException("Unknown tab $tab"),
        };
    }

    /** API-side guards: the desktop supervisors who share this service have no ARE scope. */
    public static function assertInScope(Termination $t, User $are): void
    {
        $buildingId = (int) optional($t->tenantContract)->building_id;
        if (!AreBuildings::covers($are, $buildingId)) {
            throw new NotAssignedException('This termination is outside your assigned buildings.');
        }
    }

    public static function assertMineOrUnassigned(Termination $t, User $are): void
    {
        if ((int) $t->work_flow_processes_code === TerminationWorkflow::HANDOVER_ASSIGNED && (int) $t->assigned_to !== $are->id) {
            throw new NotAssignedException('This handover is assigned to someone else.');
        }
    }

    // ---- template & detail --------------------------------------------------

    /** Works → sub-works, the checklist the desktop inspection form is built from. */
    public static function template(): array
    {
        return Work::with('subWork')->has('subWork')->orderBy('works_code')->get()->map(fn ($w) => [
            'work_id' => $w->id,
            'code' => $w->works_code,
            'desc' => $w->works_desc,
            'sub_works' => $w->subWork->map(fn ($s) => ['id' => $s->id, 'name' => $s->sub_work])->values()->all(),
        ])->values()->all();
    }

    /** The row holding the saved inspection for this contract at 503, if any. */
    public static function draftFor(Termination $t): ?Termination
    {
        if ((int) $t->work_flow_processes_code === TerminationWorkflow::HANDOVER_ASSIGNED && $t->termination_notes !== null) {
            return $t;
        }
        return Termination::where('contract_id', $t->contract_id)->where('termination_type', 1)
            ->where('work_flow_processes_code', TerminationWorkflow::HANDOVER_ASSIGNED)
            ->whereNotNull('termination_notes')->orderByDesc('id')->first();
    }

    public static function detail(Termination $t): array
    {
        $contract = $t->tenantContract()->with('building', 'unit', 'tenant')->first();
        $draft = self::draftFor($t);

        // Outstanding rent as the desktop's inspection page computes it
        $sumOfReceipt = RentOutstanding::paidForContract($t->contract_id);
        $remainAmt = totalContractRentCountCalculation($t->contract_id, $t->termination_date);
        $osAmount = $remainAmt - $sumOfReceipt - \Modules\Masters\Services\LegalWaiver::forContract($t->contract_id);   // legal settlement waiver is a credit
        $previousContracts = TenantContract::where('tenant_id', $contract->tenant_id)->where('unit_id', $contract->unit_id)
            ->where('work_flow_processes_code', 108)->where('id', '!=', $t->contract_id)->get();
        $previousOs = 0;
        $depositCheque = null;
        foreach ($previousContracts as $prev) {
            $rentAmount = $prev->tenant_contract_duration * $prev->tenant_contract_rent;
            $os = $rentAmount - RentOutstanding::paidForContract($prev->id);
            $previousOs += $os > 0 ? round($os, 3) : 0;
            if ($depositCheque === null) {
                $depositCheque = Pdc::where('tenant_contract_id', $prev->id)->where('pdc_type', 2)->orderByDesc('id')->first();
            }
        }
        $depositCheque ??= Pdc::where('tenant_contract_id', $t->contract_id)->where('pdc_type', 2)->orderByDesc('id')->first();

        $docs = TerminationDocument::where('termination_contract', $t->contract_id)->orderBy('id')->get();
        $previousInspections = Termination::where('termination_type', 1)->where('work_flow_processes_code', TerminationWorkflow::TAKEN_OVER)
            ->whereIn('contract_id', $previousContracts->pluck('id'))->orderByDesc('id')->limit(5)->get();

        return [
            'id' => $t->id,
            'stage' => (int) $t->work_flow_processes_code,
            'contract' => [
                'id' => $contract->id, 'no' => $contract->tenant_contract_no,
                'start_date' => $contract->tenant_contract_start_date, 'valid_to' => $contract->tenant_contract_valid_to_date,
                'rent' => (float) $contract->tenant_contract_rent, 'deposit' => (float) $contract->tenant_contract_deposit_amt,
            ],
            'tenant' => [
                'id' => $contract->tenant_id, 'name' => optional($contract->tenant)->tenant_name,
                'mobile' => optional($contract->tenant)->tenant_contact_no ?: optional($contract->tenant)->tenant_residence_tel,
                'email' => optional($contract->tenant)->tenant_contact_email ?: optional($contract->tenant)->tenant_personal_email,
            ],
            'building' => ['id' => $contract->building_id, 'name' => optional($contract->building)->building_name],
            'unit' => ['id' => $contract->unit_id, 'no' => optional($contract->unit)->unit_no],
            'termination_date' => $t->termination_date,
            'termination_type_status' => $t->termination_type_status,
            'overdue' => $t->termination_date ? $t->termination_date->lt(now()->startOfDay()) : false,
            'assigned_to' => $t->assigned_to,
            'outstanding_rent' => round((float) $osAmount + $previousOs, 3),
            'outstanding_rent_this_contract' => round((float) $osAmount, 3),
            'deposit_cheque' => $depositCheque ? ['id' => $depositCheque->id, 'cheque_no' => $depositCheque->pdc_check_no, 'amount' => (float) $depositCheque->pdc_amount, 'date' => $depositCheque->pdc_check_date] : null,
            'open_documents' => $docs->where('termination_doc_type', 'TerminationDocument')->map(fn ($d) => ['id' => $d->id, 'name' => $d->termination_doc_name, 'path' => $d->termination_doc])->values()->all(),
            'photos' => $docs->where('termination_doc_type', '!=', 'TerminationDocument')->map(fn ($d) => [
                'id' => $d->id, 'type' => array_search($d->termination_doc_type, self::PHOTO_TYPES, true) ?: strtolower($d->termination_doc_type),
                'path' => $d->termination_doc, 'thumb' => $d->image_path_thumbnail, 'created_at' => $d->created_at,
            ])->values()->all(),
            'previous_inspections' => $previousInspections->map(fn ($p) => ['id' => $p->id, 'contract_id' => $p->contract_id, 'taken_over_date' => $p->termination_takenover_date, 'net' => (float) $p->termination_net_amount])->values()->all(),
            'refer_back' => ($t->termination_refer_back || $t->supervisor_comment || (int) $t->is_resubmit === 2)
                ? ['comment' => $t->supervisor_comment ?: $t->termination_notes, 'at' => $t->updated_at] : null,
            'draft' => $draft ? self::draftArray($draft) : null,
        ];
    }

    private static function draftArray(Termination $d): array
    {
        return [
            'id' => $d->id,
            'lines' => TerminationChecklist::where('termination_id', $d->id)->orderBy('id')->get()->map(fn ($l) => [
                'id' => $l->id, 'work_id' => $l->work_id, 'sub_work_id' => $l->sub_work_id, 'other_work' => $l->termination_other_work,
                'quantity' => (float) $l->termination_quantity, 'amount' => (float) $l->termination_amount,
            ])->values()->all(),
            'electricity' => ['acc_no' => $d->termination_electricity_acc_no, 'close_reading' => $d->termination_electricity_close_reading, 'amount' => (float) $d->termination_electricity_amount],
            'water' => ['acc_no' => $d->termination_water_acc_no, 'close_reading' => $d->termination_water_close_reading, 'amount' => (float) $d->termination_water_amount],
            'main_key_status' => (int) $d->termination_main_key_status,
            'maintenance_due' => (float) $d->termination_discount_maintenance_due,
            'notes' => $d->termination_notes,
            'remark' => $d->termination_remark,
            'totals' => self::totalsOf($d),
            'signed' => (int) $d->termination_review_status === 1,
            'updated_at' => $d->updated_at,
        ];
    }

    public static function totalsOf(Termination $d): array
    {
        return [
            'total' => (float) $d->termination_total_amount,
            'elec_water_total' => (float) $d->termination_total_elec_water_amount,
            'maintenance_due' => (float) $d->termination_discount_maintenance_due,
            'net' => (float) $d->termination_net_amount,
        ];
    }

    // ---- draft --------------------------------------------------------------

    /**
     * Save the inspection. From 502 the ARE is assigned first (502 → 503); the
     * first save creates the 503 inspection row like the desktop's store; later
     * saves update it in place and replace the lines like the desktop's update.
     */
    public static function saveDraft(Termination $t, User $are, array $payload): Termination
    {
        $stage = (int) $t->work_flow_processes_code;
        if ($stage === TerminationWorkflow::HANDOVER_UNASSIGNED) {
            $t = TerminationWorkflow::assignHandover($t, $are, $are);
            $stage = TerminationWorkflow::HANDOVER_ASSIGNED;
        }
        if ($stage !== TerminationWorkflow::HANDOVER_ASSIGNED) {
            throw new InvalidStateException('This termination is no longer at Handover Assigned.');
        }
        $lines = collect($payload['lines'] ?? []);
        $elec = $payload['electricity'] ?? [];
        $water = $payload['water'] ?? [];
        $total = round((float) $lines->sum(fn ($l) => (float) ($l['amount'] ?? 0)), 3);
        $elecWater = round((float) ($elec['amount'] ?? 0) + (float) ($water['amount'] ?? 0), 3);
        $maintenanceDue = round((float) ($payload['maintenance_due'] ?? 0), 3);
        $net = round($total + $elecWater - $maintenanceDue, 3);

        return DB::transaction(function () use ($t, $are, $payload, $lines, $elec, $water, $total, $elecWater, $maintenanceDue, $net) {
            $draft = self::draftFor($t);
            if ($draft === null) {
                // desktop store: a new 503 row, the assignment row's users deactivated
                $t->terminationUser()->update(['status' => 0]);
                $draft = new Termination;
                $draft->contract_id = $t->contract_id;
                $draft->termination_type = 1;
                $draft->assigned_to = $t->assigned_to;
                $draft->work_flow_processes_code = TerminationWorkflow::HANDOVER_ASSIGNED;
                $draft->termination_type_status = $t->termination_type_status;
                $draft->termination_takenover_date = $t->termination_takenover_date;
                $draft->termination_date = $t->termination_date;
                $draft->os = $t->os ?? '';
                $draft->created_by = $are->id;
            } else {
                $draft->updated_by = $are->id;
            }
            $draft->termination_main_key_status = (int) ($payload['main_key_status'] ?? 0);
            $draft->termination_electricity_acc_no = $elec['acc_no'] ?? null;
            $draft->termination_electricity_close_reading = $elec['close_reading'] ?? null;
            $draft->termination_electricity_amount = round((float) ($elec['amount'] ?? 0), 3);
            $draft->termination_water_acc_no = $water['acc_no'] ?? null;
            $draft->termination_water_close_reading = $water['close_reading'] ?? null;
            $draft->termination_water_amount = round((float) ($water['amount'] ?? 0), 3);
            $draft->termination_total_amount = $total;
            $draft->termination_total_elec_water_amount = $elecWater;
            $draft->termination_discount_maintenance_due = $maintenanceDue;
            $draft->termination_net_amount = $net;
            $draft->termination_notes = $payload['notes'] ?? '';
            $draft->termination_remark = $payload['remark'] ?? $draft->termination_remark;
            $draft->save();

            if (!$draft->wasRecentlyCreated) {
                // desktop update: lines are re-created for the contract
                TerminationChecklist::where('termination_contract_id', $draft->contract_id)->delete();
            } else {
                $draft->terminationUsers()->attach($are->default_role, ['user_id' => $are->id]);
            }

            foreach ($lines as $line) {
                $row = new TerminationChecklist;
                $row->termination_id = $draft->id;
                $row->termination_contract_id = $draft->contract_id;
                if (!empty($line['sub_work_id'])) {
                    $sub = SubWork::findOrFail($line['sub_work_id']);
                    $row->work_id = $sub->works_id;
                    $row->sub_work_id = $sub->id;
                } else {
                    $row->termination_other_work = $line['other_work'] ?? 'Other';
                }
                $row->termination_amount = round((float) ($line['amount'] ?? 0), 3);
                $row->termination_quantity = (float) ($line['quantity'] ?? 1);
                $row->work_flow_processes_code = TerminationWorkflow::HANDOVER_ASSIGNED;
                $row->save();
            }

            return $draft->fresh();
        });
    }

    public static function addPhoto(Termination $t, User $are, string $path, string $thumb, string $type): TerminationDocument
    {
        $target = self::draftFor($t) ?? $t;
        return TerminationDocument::create([
            'termination_id' => $target->id,
            'termination_contract' => $t->contract_id,
            'user_id' => $are->id,
            'termination_doc' => $path,
            'termination_doc_name' => basename($path),
            'image_path_thumbnail' => $thumb,
            'termination_doc_type' => self::PHOTO_TYPES[$type] ?? ucfirst($type),
            'work_flow_processes_code' => $target->work_flow_processes_code,
            'created_by' => $are->id,
        ]);
    }

    // ---- submit -------------------------------------------------------------

    /** Signature → Taken Over (504) → PDF → tenant email. Returns the 504 row. */
    public static function submit(Termination $t, User $are, string $signatureDataUrl, ?string $tenantRemark): Termination
    {
        $draft = self::draftFor($t);
        if (!$draft || (int) $draft->work_flow_processes_code !== TerminationWorkflow::HANDOVER_ASSIGNED) {
            throw new InvalidStateException('Save the inspection first; it must be at Handover Assigned.');
        }
        // the contract must still be at 503 — nobody advanced it meanwhile
        $latest = Termination::where('contract_id', $draft->contract_id)->where('termination_type', 1)->orderByDesc('id')->first();
        if ((int) $latest->work_flow_processes_code !== TerminationWorkflow::HANDOVER_ASSIGNED) {
            throw new InvalidStateException('This termination is no longer at Handover Assigned.');
        }

        return DB::transaction(function () use ($draft, $are, $signatureDataUrl, $tenantRemark) {
            $sig = MobileImage::storeSignature($signatureDataUrl, "terminations/{$draft->id}");
            $draft->update([
                'termination_tenant_signature' => $sig,
                'termination_review_status' => 1,
                'termination_remark' => $tenantRemark ?? $draft->termination_remark,
                'termination_takenover_date' => now(),
                'os' => 'mobile',
                'updated_by' => $are->id,
            ]);
            $next = TerminationWorkflow::advance($draft->fresh(), 'ACPT', $are);
            self::pdfPath($next);

            $contract = TenantContract::with('tenant')->find($next->contract_id);
            $email = optional($contract->tenant)->tenant_contact_email ?: optional($contract->tenant)->tenant_personal_email;
            if ($email) {
                Mail::to($email)->send(new InspectionSendEmail($next, $contract, $contract->terminationChecklistOther, $contract->terminationChecklist->groupBy('work_id')));
            }
            return $next;
        });
    }

    /** Renders the inspection to a cached PDF under mobile/terminations/{id}. */
    public static function pdfPath(Termination $t): string
    {
        $path = "mobile/terminations/{$t->id}/inspection.pdf";
        $disk = Storage::disk('local');
        if ($disk->exists($path) && $disk->lastModified($path) >= ($t->updated_at?->timestamp ?? 0)) {
            return $path;
        }
        $contract = TenantContract::with('building', 'unit', 'tenant', 'building.location')->find($t->contract_id);
        $data = [
            'termination' => $t,
            'tenantContract' => $contract,
            'lines' => TerminationChecklist::with('work', 'subWorks')->where('termination_contract_id', $t->contract_id)->orderBy('id')->get(),
            'documents' => TerminationDocument::where('termination_contract', $t->contract_id)->where('termination_doc_type', '!=', 'TerminationDocument')->orderBy('id')->get(),
            'printedAt' => now()->format('d/m/Y H:i'),
        ];
        $disk->makeDirectory(dirname($path));
        $disk->put($path, \PDF::loadView('backoffice::Termination.inspection_pdf', $data)->setPaper('a4')->output());
        return $path;
    }
}
