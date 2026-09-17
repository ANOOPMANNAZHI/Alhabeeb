<?php

namespace Modules\BackOffice\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\BackOffice\Entities\TerminationDues;
use Modules\BackOffice\Entities\TerminationDuesAllocation;
use Modules\BackOffice\Entities\TerminationDuesLine;
use Modules\BackOffice\Services\TerminationDuesCategory as Cat;
use Modules\BackOffice\Services\TerminationDuesService;

/**
 * Termination dues: the receivable left behind by a terminated tenant.
 * Back Office follows up rent / tax / E&W / other; Maintenance follows up
 * checklist charges. Balances are recomputed on every page view.
 */
class TerminationDuesController extends Controller
{
    const PER_PAGE = 25;

    public function __construct()
    {
        $this->middleware('auth');
        // summary feeds the box on the receipt / deposit refund forms; any signed-in user may read it
        $this->middleware('permission:view_termination_dues_backoffice|view_termination_dues_maintenance')->except('summary');
    }

    /** Teams the current user may see, in tab order. */
    private function visibleTeams()
    {
        $user = \Auth::user();
        $teams = [];
        if ($user->can('view_termination_dues_backoffice')) {
            $teams[] = Cat::TEAM_BACKOFFICE;
        }
        if ($user->can('view_termination_dues_maintenance')) {
            $teams[] = Cat::TEAM_MAINTENANCE;
        }
        return $teams;
    }

    /**
     * One list for both teams, laid out like the other list pages
     * (quick column filters + advance search + AJAX refresh + sortable).
     */
    public function index(Request $request)
    {
        // Default to records that still have a balance; "all" clears it
        if (!$request->has('status')) {
            $request->merge(['status' => 'outstanding']);
        }
        if ($request->input('status') === 'all') {
            $request->merge(['status' => '']);
        }

        $dues = TerminationDues::with(['tenantContract.tenant', 'tenantContract.building', 'tenantContract.unit'])
            ->filter($request)
            ->sortable(['balance' => 'desc'])
            ->paginate(self::PER_PAGE);

        $enquiry_fields = [
            'tenantContract__tenant_contract_no' => 'Contract No',
            'tenant__tenant_name'                => 'Tenant Name',
            'tenant__tenant_contact_no'          => 'Tenant Mobile',
            'building__building_name'            => 'Building Name',
            'unit__unit_no'                      => 'Unit No',
            'termination_date'                   => 'Terminated On',
            'total_owed'                         => 'Owed',
            'total_settled'                      => 'Settled',
            'balance'                            => 'Balance',
            'status'                             => 'Status (open / partial / settled / written_off)',
            'next_promise_date'                  => 'Promised Date',
        ];
        $operations = [
            '='         => ' Is equal to ',
            '!='        => ' Is not equal to ',
            '>'         => ' Is greater than ',
            '>='        => ' Is greater than or equal to ',
            '<'         => ' Is less than ',
            '<='        => ' Is less than or equal to',
            'ilike'     => ' Like ',
            'ilike%...%' => ' Like%...% ',
        ];
        $request->flash();

        $quick_url = $route = route('termination-dues.index');
        $statuses = [
            'outstanding' => 'With balance',
            'open'        => 'Open',
            'partial'     => 'Partially paid',
            'settled'     => 'Settled',
            'written_off' => 'Written off',
            'all'         => 'All',
        ];

        if (isset($request->ajax)) {
            return view('backoffice::TerminationDues.index_ajax', compact('dues', 'request', 'route'));
        }
        return view('backoffice::TerminationDues.index', compact('dues', 'enquiry_fields', 'operations', 'quick_url', 'statuses'));
    }

    public function show(TerminationDues $terminationDues)
    {
        $terminationDues->load(['lines', 'followups.creator', 'tenantContract.tenant', 'tenantContract.building', 'tenantContract.unit', 'termination']);
        $result = (new TerminationDuesService)->refresh($terminationDues);
        $manual = TerminationDuesAllocation::with('creator')->whereIn('termination_dues_line_id', $terminationDues->lines->pluck('id'))->orderBy('id')->get();
        $teams = $this->visibleTeams();

        return view('backoffice::TerminationDues.show', [
            'dues'       => $terminationDues,
            'r'          => $result,
            'manual'     => $manual,
            'teams'      => $teams,
            'methods'    => ['call' => 'Phone call', 'sms' => 'SMS', 'whatsapp' => 'WhatsApp', 'email' => 'Email', 'visit' => 'Visit', 'other' => 'Other'],
        ]);
    }

    public function storeFollowup(Request $request, TerminationDues $terminationDues)
    {
        $this->validate($request, [
            'followup_date' => 'required|date',
            'method'        => 'required|in:call,sms,whatsapp,email,visit,other',
            'note'          => 'nullable|string|max:2000',
            'promise_date'  => 'nullable|date',
        ]);
        // The team is the signed-in user's own; users with both permissions are filed under Back Office.
        $teams = $this->visibleTeams();
        $ownerTeam = in_array(Cat::TEAM_BACKOFFICE, $teams, true) ? Cat::TEAM_BACKOFFICE : (count($teams) ? $teams[0] : Cat::TEAM_BACKOFFICE);

        $terminationDues->followups()->create([
            'owner_team'    => $ownerTeam,
            'followup_date' => $request->followup_date,
            'method'        => $request->method,
            'note'          => $request->note,
            'promise_date'  => $request->promise_date ?: null,
            'created_by'    => \Auth::user()->id,
        ]);
        (new TerminationDuesService)->refresh($terminationDues);

        return redirect()->route('termination-dues.show', $terminationDues->id)->with('success', 'Follow-up recorded.');
    }

    /** Pin an unallocated receipt line / deduction to one of this record's lines. */
    public function storeAllocation(Request $request, TerminationDues $terminationDues)
    {
        $this->validate($request, [
            'line_id'     => 'required|integer',
            'source_type' => 'required|in:rent_receipt,general_receipt_line,deposit_deduction',
            'source_id'   => 'required|integer',
            'amount'      => 'required|numeric|min:0.001',
            'remark'      => 'nullable|string|max:1000',
        ]);
        $line = TerminationDuesLine::where('termination_dues_id', $terminationDues->id)->where('id', $request->line_id)->firstOrFail();
        $this->authorizeTeam($line->owner_team);

        $exists = TerminationDuesAllocation::where('source_type', $request->source_type)->where('source_id', $request->source_id)
            ->whereIn('termination_dues_line_id', $terminationDues->lines()->pluck('id'))->exists();
        if ($exists) {
            return redirect()->route('termination-dues.show', $terminationDues->id)->with('error', 'That receipt line is already assigned. Remove the existing assignment first.');
        }

        TerminationDuesAllocation::create([
            'termination_dues_line_id' => $line->id,
            'source_type'              => $request->source_type,
            'source_id'                => $request->source_id,
            'amount'                   => $request->amount,
            'remark'                   => $request->remark,
            'created_by'               => \Auth::user()->id,
        ]);
        (new TerminationDuesService)->refresh($terminationDues);

        return redirect()->route('termination-dues.show', $terminationDues->id)->with('success', 'Receipt assigned to "' . $line->description . '".');
    }

    /**
     * Waive amounts on one or more lines in a single approval.
     * Form posts lines[<line_id>][selected]=1 and lines[<line_id>][amount].
     */
    public function storeWaiver(Request $request, TerminationDues $terminationDues)
    {
        $this->validate($request, [
            'lines'            => 'required|array',
            'lines.*.amount'   => 'nullable|numeric|min:0.001',
            'remark'           => 'required|string|max:1000',
        ]);

        $picked = [];
        foreach ((array) $request->input('lines', []) as $lineId => $row) {
            if (!empty($row['selected'])) {
                $picked[(int) $lineId] = isset($row['amount']) ? (float) $row['amount'] : 0.0;
            }
        }
        if (empty($picked)) {
            return redirect()->route('termination-dues.show', $terminationDues->id)->with('error', 'Tick at least one line to waive.');
        }

        $service  = new TerminationDuesService;
        $balances = $service->refresh($terminationDues)['lines'];
        $lines    = TerminationDuesLine::where('termination_dues_id', $terminationDues->id)->whereIn('id', array_keys($picked))->get()->keyBy('id');

        $errors = [];
        foreach ($picked as $lineId => $amount) {
            if (!isset($lines[$lineId])) {
                $errors[] = 'Line #' . $lineId . ' does not belong to this record.';
                continue;
            }
            $line = $lines[$lineId];
            $this->authorizeTeam($line->owner_team);
            $balance = isset($balances[$lineId]) ? (float) $balances[$lineId]['balance'] : 0.0;
            if ($amount <= 0) {
                $errors[] = 'Enter an amount for "' . $line->description . '".';
            } elseif ($amount > $balance + 0.005) {
                $errors[] = 'Waiver on "' . $line->description . '" (' . numberFormat($amount) . ') exceeds its open balance of ' . numberFormat($balance) . '.';
            }
        }
        if ($errors) {
            return redirect()->route('termination-dues.show', $terminationDues->id)->with('error', implode(' ', $errors));
        }

        $total = 0.0;
        \DB::transaction(function () use ($picked, $lines, $request, &$total) {
            foreach ($picked as $lineId => $amount) {
                TerminationDuesAllocation::create([
                    'termination_dues_line_id' => $lineId,
                    'source_type'              => 'waiver',
                    'source_id'                => null,
                    'amount'                   => round($amount, 3),
                    'remark'                   => $request->remark,
                    'created_by'               => \Auth::user()->id,
                ]);
                $total += $amount;
            }
        });
        $service->refresh($terminationDues);

        $names = $lines->only(array_keys($picked))->pluck('description')->all();
        return redirect()->route('termination-dues.show', $terminationDues->id)
            ->with('success', numberFormat($total) . ' OMR waived on ' . count($picked) . ' line' . (count($picked) === 1 ? '' : 's') . ': ' . implode(', ', $names) . '.');
    }

    public function destroyAllocation(TerminationDues $terminationDues, $allocation)
    {
        $alloc = TerminationDuesAllocation::whereIn('termination_dues_line_id', $terminationDues->lines()->pluck('id'))->findOrFail($allocation);
        $this->authorizeTeam($alloc->line->owner_team);
        $alloc->delete();
        (new TerminationDuesService)->refresh($terminationDues);

        return redirect()->route('termination-dues.show', $terminationDues->id)->with('success', 'Assignment removed.');
    }

    /**
     * Printable statement: tenant / building / unit / contract, then one flat
     * settlement table (no team split) and the balance.
     */
    public function print(TerminationDues $terminationDues)
    {
        $terminationDues->load(['lines', 'tenantContract.tenant', 'tenantContract.building.location', 'tenantContract.unit']);
        $r = (new TerminationDuesService)->refresh($terminationDues);
        $c = $terminationDues->tenantContract;

        $lines = [];
        $total = ['owed' => 0.0, 'deposit' => 0.0, 'receipts' => 0.0, 'waived' => 0.0, 'balance' => 0.0];
        foreach ($terminationDues->lines as $line) {
            $l = isset($r['lines'][$line->id]) ? $r['lines'][$line->id] : null;
            if (!$l) {
                continue;
            }
            $credit = !empty($l['credit']);
            // Older maintenance lines were stored as "- Sub work" when the work had no description
            $desc  = trim(preg_replace('/^[\s\-–·]+/u', '', $line->description));
            $label = Cat::label($line->category);
            $lines[] = [
                'description' => strcasecmp($desc, $label) === 0 ? $desc : $desc . ' (' . $label . ')',
                'owed'        => $l['owed'],
                'deposit'     => $l['deposit'],
                'receipts'    => $l['receipts'],
                'waived'      => $l['waived'],
                'balance'     => $l['balance'],
                'credit'      => $credit,
            ];
            $total['owed']     += $l['owed'];
            if (!$credit) {
                $total['deposit']  += $l['deposit'];
                $total['receipts'] += $l['receipts'];
                $total['waived']   += $l['waived'];
                $total['balance']  += $l['balance'];
            }
        }

        $data = [
            'tenantName'      => optional(optional($c)->tenant)->tenant_name ?: '-',
            'tenantMobile'    => optional(optional($c)->tenant)->tenant_contact_no,
            'buildingName'    => optional(optional($c)->building)->building_name ?: '-',
            'location'        => optional(optional(optional($c)->building)->location)->locations_name,
            'unitNo'          => optional(optional($c)->unit)->unit_no ?: (optional(optional($c)->unit)->unit_code ?: '-'),
            'contractNo'      => optional($c)->tenant_contract_no ?: ('#' . $terminationDues->tenant_contract_id),
            'terminationDate' => $terminationDues->termination_date ? $terminationDues->termination_date->format('d/m/Y') : null,
            'status'          => $r['status'],
            'printedAt'       => date('d/m/Y H:i'),
            'lines'           => $lines,
            'total'           => array_map(function ($v) { return round($v, 3); }, $total),
        ];

        $pdf = \PDF::loadView('backoffice::TerminationDues.pdf', $data)->setPaper('a4', 'portrait');
        return $pdf->stream('termination-dues-' . preg_replace('/[^A-Za-z0-9_-]/', '', $data['contractNo']) . '.pdf');
    }

    /** JSON for the summary box on receipt / deposit refund forms. */
    public function summary(Request $request)
    {
        $contractId = (int) $request->get('contract_id');
        if (!$contractId) {
            return response()->json(['found' => false]);
        }
        $summary = (new TerminationDuesService)->summaryForContract($contractId);
        if (!$summary) {
            return response()->json(['found' => false]);
        }
        foreach ($summary['categories'] as &$c) {
            $c['owed_fmt'] = numberFormat($c['owed']);
            $c['settled_fmt'] = numberFormat($c['settled']);
            $c['balance_fmt'] = numberFormat($c['balance']);
        }
        unset($c);
        $summary['balance_fmt'] = numberFormat($summary['balance']);
        $summary['found'] = true;
        return response()->json($summary);
    }

    private function authorizeTeam($team)
    {
        $permission = $team === Cat::TEAM_MAINTENANCE ? 'view_termination_dues_maintenance' : 'view_termination_dues_backoffice';
        if (!\Auth::user()->can($permission)) {
            abort(403, 'You can only act on ' . Cat::teamLabel($team) . ' dues.');
        }
    }
}
