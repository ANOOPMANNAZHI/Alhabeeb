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
        $this->middleware('permission:view_termination_dues_backoffice|view_termination_dues_maintenance');
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

    public function index(Request $request)
    {
        $teams = $this->visibleTeams();
        $tab = $request->get('team', count($teams) === 1 ? $teams[0] : 'all');
        if ($tab !== 'all' && !in_array($tab, $teams, true)) {
            $tab = count($teams) === 1 ? $teams[0] : 'all';
        }
        $status = $request->get('status', 'outstanding'); // outstanding | open | partial | settled | written_off | all
        $q = trim((string) $request->get('q', ''));

        $balanceColumn = $tab === Cat::TEAM_MAINTENANCE ? 'maintenance_balance' : ($tab === Cat::TEAM_BACKOFFICE ? 'backoffice_balance' : 'balance');

        $query = TerminationDues::with(['tenantContract.tenant', 'tenantContract.building', 'tenantContract.unit']);
        if ($status === 'outstanding') {
            $query->where($balanceColumn, '>', 0);
        } elseif ($status !== 'all') {
            $query->where('status', $status);
            if ($tab !== 'all') {
                $query->where($balanceColumn, '>', 0);
            }
        } elseif ($tab !== 'all') {
            // "all" statuses but a single team: only records that ever had this team's lines
            $query->whereHas('lines', function ($l) use ($tab) { $l->where('owner_team', $tab); });
        }
        if ($q !== '') {
            $query->whereHas('tenantContract', function ($c) use ($q) {
                $c->where('tenant_contract_no', 'ILIKE', '%' . $q . '%')
                  ->orWhereHas('tenant', function ($t) use ($q) {
                      $t->where('tenant_name', 'ILIKE', '%' . $q . '%')->orWhere('tenant_contact_no', 'ILIKE', '%' . $q . '%');
                  });
            });
        }
        $dues = $query->orderBy($balanceColumn, 'desc')->orderBy('termination_date')->paginate(self::PER_PAGE)->appends($request->query());

        $counts = [];
        foreach (array_merge($teams, ['all']) as $t) {
            $col = $t === Cat::TEAM_MAINTENANCE ? 'maintenance_balance' : ($t === Cat::TEAM_BACKOFFICE ? 'backoffice_balance' : 'balance');
            $counts[$t] = TerminationDues::where($col, '>', 0)->count();
        }

        return view('backoffice::TerminationDues.index', compact('dues', 'teams', 'tab', 'status', 'q', 'counts', 'balanceColumn'));
    }

    public function show(TerminationDues $terminationDues)
    {
        $terminationDues->load(['lines', 'followups.creator', 'tenantContract.tenant', 'tenantContract.building', 'tenantContract.unit', 'termination']);
        $result = (new TerminationDuesService)->refresh($terminationDues);
        $manual = TerminationDuesAllocation::with('creator')->whereIn('termination_dues_line_id', $terminationDues->lines->pluck('id'))->orderBy('id')->get();
        $teams = $this->visibleTeams();

        // Which source keys are pinned manually (for the source list)
        $pinnedKeys = [];
        foreach ($manual as $m) {
            if ($m->source_type !== 'waiver') {
                $pinnedKeys[$m->source_type . ':' . $m->source_id] = $m;
            }
        }

        return view('backoffice::TerminationDues.show', [
            'dues'       => $terminationDues,
            'r'          => $result,
            'manual'     => $manual,
            'pinnedKeys' => $pinnedKeys,
            'teams'      => $teams,
            'methods'    => ['call' => 'Phone call', 'sms' => 'SMS', 'whatsapp' => 'WhatsApp', 'email' => 'Email', 'visit' => 'Visit', 'other' => 'Other'],
        ]);
    }

    public function storeFollowup(Request $request, TerminationDues $terminationDues)
    {
        $this->validate($request, [
            'owner_team'    => 'required|in:backoffice,maintenance',
            'followup_date' => 'required|date',
            'method'        => 'required|in:call,sms,whatsapp,email,visit,other',
            'note'          => 'nullable|string|max:2000',
            'promise_date'  => 'nullable|date',
        ]);
        $this->authorizeTeam($request->owner_team);

        $terminationDues->followups()->create([
            'owner_team'    => $request->owner_team,
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

    public function storeWaiver(Request $request, TerminationDues $terminationDues)
    {
        $this->validate($request, [
            'line_id' => 'required|integer',
            'amount'  => 'required|numeric|min:0.001',
            'remark'  => 'required|string|max:1000',
        ]);
        $line = TerminationDuesLine::where('termination_dues_id', $terminationDues->id)->where('id', $request->line_id)->firstOrFail();
        $this->authorizeTeam($line->owner_team);

        TerminationDuesAllocation::create([
            'termination_dues_line_id' => $line->id,
            'source_type'              => 'waiver',
            'source_id'                => null,
            'amount'                   => $request->amount,
            'remark'                   => $request->remark,
            'created_by'               => \Auth::user()->id,
        ]);
        (new TerminationDuesService)->refresh($terminationDues);

        return redirect()->route('termination-dues.show', $terminationDues->id)->with('success', numberFormat($request->amount) . ' waived on "' . $line->description . '".');
    }

    public function destroyAllocation(TerminationDues $terminationDues, $allocation)
    {
        $alloc = TerminationDuesAllocation::whereIn('termination_dues_line_id', $terminationDues->lines()->pluck('id'))->findOrFail($allocation);
        $this->authorizeTeam($alloc->line->owner_team);
        $alloc->delete();
        (new TerminationDuesService)->refresh($terminationDues);

        return redirect()->route('termination-dues.show', $terminationDues->id)->with('success', 'Assignment removed.');
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
