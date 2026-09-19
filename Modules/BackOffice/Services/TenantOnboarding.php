<?php

namespace Modules\BackOffice\Services;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\BackOffice\Entities\OnboardingChecklistItem;
use Modules\BackOffice\Entities\TenantOnboarding as Model;
use Modules\BackOffice\Entities\TenantOnboardingEntry;
use Modules\BackOffice\Entities\TenantOnboardingPhoto;
use Modules\BackOffice\Notifications\OnboardingCompletedNotification;
use Modules\Maintenance\Exceptions\InvalidStateException;
use Modules\Maintenance\Exceptions\NotAssignedException;
use Modules\Masters\Services\AreBuildings;
use Modules\MobileApi\Services\MobileImage;
use Modules\Sales\Entities\TenantContract;

/**
 * Tenant onboarding (move-in) checklist. One row per active contract, created
 * on activation (stage 108) and back-filled by `onboarding:sync`; the ARE of
 * the building fills the configurable items on the phone, the tenant signs,
 * back-office verifies.
 */
class TenantOnboarding
{
    public const OPEN = 'open';
    public const COMPLETED = 'completed';
    public const VERIFIED = 'verified';
    public const CANCELLED = 'cancelled';

    /** The ARE currently responsible for a building (latest date-valid assignment). */
    public static function areForBuilding(int $buildingId): ?User
    {
        $today = Carbon::today()->toDateString();
        $userId = DB::table('are_buildings as ab')->join('preferred_buildings as pb', 'pb.are_building_id', '=', 'ab.id')
            ->where('pb.building_id', $buildingId)
            ->where(fn ($q) => $q->whereNull('ab.assign_from')->orWhereDate('ab.assign_from', '<=', $today))
            ->where(fn ($q) => $q->whereNull('ab.assign_too')->orWhereDate('ab.assign_too', '>=', $today))
            ->where(fn ($q) => $q->whereNull('pb.assign_from')->orWhereDate('pb.assign_from', '<=', $today))
            ->where(fn ($q) => $q->whereNull('pb.assign_to')->orWhereDate('pb.assign_to', '>=', $today))
            ->orderByDesc('ab.assign_from')->orderByDesc('ab.id')->value('ab.user_id');
        return $userId ? User::find($userId) : null;
    }

    /** Null when the contract is not active or already has an onboarding. */
    public static function createForContract(TenantContract $c): ?Model
    {
        if ((int) $c->work_flow_processes_code !== 108 || (int) $c->tenant_contract_status !== 1) {
            return null;
        }
        if (Model::where('tenant_contract_id', $c->id)->exists()) {
            return null;
        }
        return Model::create([
            'tenant_contract_id' => $c->id, 'unit_id' => $c->unit_id, 'building_id' => $c->building_id,
            'assigned_user_id' => optional(self::areForBuilding((int) $c->building_id))->id, 'status' => self::OPEN,
        ]);
    }

    /** Back-fill: contracts activated (updated) since $since that have no onboarding yet. */
    public static function sync(Carbon $since): int
    {
        $n = 0;
        TenantContract::where('work_flow_processes_code', 108)->where('tenant_contract_status', 1)->where('updated_at', '>=', $since)
            ->whereNotIn('id', Model::query()->select('tenant_contract_id'))
            ->orderBy('id')->chunkById(200, function ($contracts) use (&$n) {
                foreach ($contracts as $c) {
                    if (self::createForContract($c)) {
                        $n++;
                    }
                }
            });
        return $n;
    }

    // ---- queue --------------------------------------------------------------

    public static function queue(User $are, string $tab, ?int $buildingId = null): Builder
    {
        $buildings = AreBuildings::forUser($are);
        if ($buildingId) {
            $buildings = $buildings->intersect([$buildingId]);
        }
        return Model::with('tenantContract.tenant', 'tenantContract.unit', 'tenantContract.building', 'assignedUser')
            ->where(fn ($q) => $q->where('assigned_user_id', $are->id)->orWhereIn('building_id', $buildings->all()))
            ->when($buildingId, fn ($q) => $q->where('building_id', $buildingId))
            ->when($tab === 'open', fn ($q) => $q->where('status', self::OPEN))
            ->when($tab === 'completed', fn ($q) => $q->whereIn('status', [self::COMPLETED, self::VERIFIED]))
            ->orderByDesc('id');
    }

    public static function assertMine(Model $o, User $are): void
    {
        if ((int) $o->assigned_user_id !== $are->id && !AreBuildings::covers($are, (int) $o->building_id)) {
            throw new NotAssignedException('This onboarding is outside your assigned buildings.');
        }
    }

    // ---- detail & entries ---------------------------------------------------

    /** Active items merged with the saved entries and photos, plus progress. */
    public static function detail(Model $o): array
    {
        $items = OnboardingChecklistItem::active()->get();
        $entries = $o->entries()->with('photos')->get()->keyBy('item_id');
        $rows = $items->map(function ($i) use ($entries) {
            $e = $entries->get($i->id);
            return [
                'item_id' => $i->id, 'group' => $i->group_name, 'title' => $i->title, 'input_type' => $i->input_type,
                'required' => (bool) $i->required, 'needs_photo' => (bool) $i->needs_photo,
                'entry' => $e ? ['value' => $e->value, 'remark' => $e->remark, 'done' => (bool) $e->done, 'not_applicable' => (bool) $e->not_applicable] : null,
                'photos' => $e ? $e->photos->map(fn ($p) => ['id' => $p->id, 'path' => $p->path, 'thumb' => $p->thumbnail, 'created_at' => $p->created_at])->values()->all() : [],
            ];
        })->values();
        $done = $rows->filter(fn ($r) => $r['entry'] && ($r['entry']['done'] || $r['entry']['not_applicable']))->count();
        return ['items' => $rows->all(), 'progress' => ['done' => $done, 'total' => $rows->count()]];
    }

    /** @param array<int, array{item_id:int, value?:string, remark?:string, done?:bool, not_applicable?:bool}> $entries */
    public static function saveEntries(Model $o, User $are, array $entries): Model
    {
        self::assertMine($o, $are);
        if ($o->status !== self::OPEN) {
            throw new InvalidStateException('This onboarding is already ' . $o->status . '.');
        }
        DB::transaction(function () use ($o, $are, $entries) {
            foreach ($entries as $e) {
                TenantOnboardingEntry::updateOrCreate(
                    ['tenant_onboarding_id' => $o->id, 'item_id' => (int) $e['item_id']],
                    [
                        'value' => $e['value'] ?? null, 'remark' => $e['remark'] ?? null,
                        'done' => (bool) ($e['done'] ?? false), 'not_applicable' => (bool) ($e['not_applicable'] ?? false),
                    ]
                );
            }
            $o->update(['started_at' => $o->started_at ?? now(), 'assigned_user_id' => $o->assigned_user_id ?? $are->id]);
        });
        return $o->fresh();
    }

    public static function addPhoto(Model $o, User $are, int $itemId, string $path, string $thumb): TenantOnboardingPhoto
    {
        self::assertMine($o, $are);
        if ($o->status !== self::OPEN) {
            throw new InvalidStateException('This onboarding is already ' . $o->status . '.');
        }
        $entry = TenantOnboardingEntry::firstOrCreate(['tenant_onboarding_id' => $o->id, 'item_id' => $itemId]);
        return TenantOnboardingPhoto::create(['entry_id' => $entry->id, 'path' => $path, 'thumbnail' => $thumb, 'created_by' => $are->id]);
    }

    // ---- complete / verify --------------------------------------------------

    /** Required items must be done (or N/A); then signature, PDF, back-office notification. */
    public static function complete(Model $o, User $are, string $signatureDataUrl, ?string $remark): Model
    {
        self::assertMine($o, $are);
        if ($o->status !== self::OPEN) {
            throw new InvalidStateException('This onboarding is already ' . $o->status . '.');
        }
        $detail = self::detail($o);
        $missing = collect($detail['items'])
            ->filter(fn ($r) => $r['required'] && !($r['entry'] && ($r['entry']['done'] || $r['entry']['not_applicable'])))
            ->pluck('title');
        if ($missing->isNotEmpty()) {
            throw new InvalidStateException('Required items not completed: ' . $missing->implode(', '));
        }

        return DB::transaction(function () use ($o, $are, $signatureDataUrl, $remark) {
            $o->update([
                'tenant_signature' => MobileImage::storeSignature($signatureDataUrl, "onboarding/{$o->id}"),
                'tenant_remark' => $remark, 'status' => self::COMPLETED, 'completed_at' => now(),
                'assigned_user_id' => $o->assigned_user_id ?? $are->id,
            ]);
            $o->update(['pdf_path' => self::pdfPath($o->fresh())]);
            self::notifyBackOffice($o->fresh());
            return $o->fresh();
        });
    }

    public static function verify(Model $o, User $by): void
    {
        if ($o->status !== self::COMPLETED) {
            throw new InvalidStateException('Only completed onboardings can be verified.');
        }
        $o->update(['status' => self::VERIFIED, 'verified_by' => $by->id, 'verified_at' => now()]);
    }

    public static function reassign(Model $o, User $to): void
    {
        $o->update(['assigned_user_id' => $to->id]);
    }

    /** Renders (and re-renders) the onboarding report under mobile/onboarding/{id}. */
    public static function pdfPath(Model $o): string
    {
        $path = "mobile/onboarding/{$o->id}/onboarding.pdf";
        $disk = Storage::disk('local');
        $disk->makeDirectory(dirname($path));
        $o->load('tenantContract.tenant', 'tenantContract.unit', 'tenantContract.building', 'assignedUser.employee');
        $disk->put($path, \PDF::loadView('backoffice::Onboarding.pdf', [
            'onboarding' => $o, 'detail' => self::detail($o), 'printedAt' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4')->output());
        return $path;
    }

    private static function notifyBackOffice(Model $o): void
    {
        $contract = $o->tenantContract;
        foreach (User::role(['backoffice_manager', 'backoffice_executive'])->get() as $u) {
            $u->notify(new OnboardingCompletedNotification($o, $contract));
        }
    }
}
