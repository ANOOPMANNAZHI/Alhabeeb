<?php

namespace Modules\Masters\Http\Controllers;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\BackOffice\Services\LegalMessages;
use Modules\Masters\Entities\Legal;
use Modules\Masters\Services\LegalCase;
use Modules\Masters\Services\LegalSettlement;
use Modules\Masters\Services\LegalWaiver;
use Modules\General\Http\Controllers\GeneralController as General;

/**
 * Legal case page (plan 2026-10-13, phase 2): summary, amounts, court details, stage stepper and
 * update, comments with files, timeline; and the Legal Stages master.
 */
class LegalCaseController extends Controller
{
    const FILE_RULE = 'file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240';

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show($id)
    {
        abort_unless(LegalCase::canView(\Auth::user()), 403);
        $legal = Legal::with(['tenantContract.tenant', 'building', 'unit', 'lawyer', 'workFlowProcess'])->findOrFail($id);
        // no case page before the lawyer accepted the case: open the screen of the step it is at
        if (!LegalCase::hasCasePage($legal)) {
            return redirect(LegalCase::screenUrl($legal));
        }
        clearNotification('Modules\Masters\Notifications\LegalNotification', $legal->id);

        $user = \Auth::user();
        $settlement = LegalSettlement::current($legal->id);
        $settlementStatus = $settlement ? (int) $settlement->status : null;
        return view('masters::Legal.case_page', [
            'settlement' => $settlement,
            'schedule' => $settlement && in_array($settlementStatus, [LegalSettlement::ACTIVE, LegalSettlement::COMPLETED, LegalSettlement::BROKEN], true)
                ? LegalSettlement::schedule($settlement, $legal->tenant_contract_id) : null,
            'pendingRows' => $settlement ? LegalSettlement::installments($settlement->id) : collect(),
            'settlementReceipts' => $settlement ? LegalSettlement::receipts($settlement, $legal->tenant_contract_id) : collect(),
            'settlementHistory' => LegalSettlement::all($legal->id)->filter(function ($s) use ($settlement) { return !$settlement || $s->id !== $settlement->id; }),
            'canEnterSettlement' => $user->can('legal_settlement_enter') && LegalCase::canUpdateStage($user, $legal)
                && (LegalSettlement::canEnter($legal) || $settlementStatus === LegalSettlement::SENT_BACK),
            'canVerify' => $user->can('legal_settlement_verify') && !$legal->closed_at && $settlementStatus === LegalSettlement::PENDING
                && ((int) $settlement->entered_by !== (int) $user->id || $user->hasRole('super_admin')),
            'canClose' => $user->can('legal_case_close') && LegalCase::isActive($legal),
            'writeOff' => $settlement && in_array($settlementStatus, [LegalSettlement::ACTIVE, LegalSettlement::BROKEN, LegalSettlement::COMPLETED], true)
                ? LegalSettlement::writeOffAmount($settlement, $legal) : 0.0,
            'liveWaiver' => LegalWaiver::liveForCase($legal->id),            'canReopen' => ($user->can('legal_settlement_verify') || $user->can('plms_approve')) && $legal->closed_at
                && $legal->close_reason !== 'handed_over' && !LegalCase::openCaseFor($legal->tenant_contract_id),
            'judgmentReached' => LegalSettlement::judgmentReached($legal),
            'legal' => $legal,
            'timeline' => LegalCase::timeline($legal->id),
            'docs' => LegalCase::documentsByUpdate($legal->id),
            'outstanding' => LegalCase::outstanding($legal),
            'owed' => LegalCase::owed($legal),
            'defaultHandledBy' => LegalCase::defaultHandledBy($legal->building_id),
            'stages' => LegalCase::stagesFor($legal),
            'current' => LegalCase::stage($legal->stage_id),
            'canStage' => LegalCase::isActive($legal) && LegalCase::canUpdateStage($user, $legal),
            'canComment' => !$legal->closed_at && $user->can('legal_comment_add'),
            'related' => Legal::where('tenant_contract_id', $legal->tenant_contract_id)->where('id', '<>', $legal->id)->orderBy('id')->get(),
        ]);
    }

    /*
     * The case page actions below post from modals: their errors go to the 'modal' error bag / 'modal_error'
     * flash so the reopened modal shows them (the layout's page alert reads only the default bag and 'error').
     */
    public function stageStore(Request $request, $id)
    {
        $legal = Legal::findOrFail($id);
        $user = \Auth::user();
        abort_unless(LegalCase::canUpdateStage($user, $legal), 403);

        $stage = LegalCase::stage((int) $request->stage_id);
        if ($error = LegalCase::stageError($legal, $stage)) {
            return redirect()->back()->withInput()->with('modal_error', $error);
        }
        $rules = [
            'event_date' => 'required|date|before_or_equal:today',
            'note' => 'required|max:2000',
            'next_hearing_date' => 'nullable|date|after_or_equal:today',
            'files.*' => self::FILE_RULE,
        ];
        if ($stage->code === 'filed') {
            $rules += ['court_case_no' => 'required|max:100', 'court_name' => 'nullable|max:150', 'claim_amount' => 'nullable|numeric|min:0'];
        }
        if ($stage->code === 'judgment') {
            $rules += ['judgment_amount' => 'required|numeric|min:0'];
            if ((int) $legal->case_type === 1) {
                $rules += ['eviction_ordered' => 'required|in:0,1'];
            }
        }
        $this->validateWithBag('modal', $request, $rules, [
            'event_date.before_or_equal' => 'The stage date cannot be in the future.',
            'next_hearing_date.after_or_equal' => 'The next hearing date cannot be in the past.',
        ]);

        $details = [];
        $changes = ['stage_id' => $stage->id, 'next_hearing_date' => $request->next_hearing_date ?: null];
        if ($stage->code === 'filed') {
            $claim = $request->claim_amount !== null && $request->claim_amount !== '' ? round((float) $request->claim_amount, 3) : LegalCase::owed($legal)['total'];
            $changes += ['court_case_no' => $request->court_case_no, 'court_name' => $request->court_name,
                         'filed_date' => $request->event_date, 'claim_amount' => $claim];
            $details[] = 'Case no. ' . $request->court_case_no . ($request->court_name ? ' at ' . $request->court_name : '')
                . ', claim ' . numberFormat($claim);
        }
        if ($stage->code === 'judgment') {
            $changes += ['judgment_amount' => round((float) $request->judgment_amount, 3), 'judgment_date' => $request->event_date,
                         'eviction_ordered' => (int) $legal->case_type === 1 ? (bool) $request->eviction_ordered : null];
            $details[] = 'Amount awarded ' . numberFormat($request->judgment_amount)
                . ((int) $legal->case_type === 1 ? ', eviction ' . ($request->eviction_ordered ? 'ordered' : 'not ordered') : '');
        }
        if ($request->next_hearing_date) {
            $details[] = 'Next hearing ' . date('d/m/Y', strtotime($request->next_hearing_date));
        }
        $note = trim($request->note . ($details ? "\n" . implode('. ', $details) . '.' : ''));

        DB::transaction(function () use ($legal, $stage, $changes, $note, $request, $user) {
            $legal->update($changes);
            $updateId = LegalCase::log($legal->id, 'stage', $note, $user->id,
                ['stage_id' => $stage->id, 'event_date' => $request->event_date, 'next_date' => $request->next_hearing_date ?: null]);
            LegalCase::storeFiles($legal->id, $updateId, $request->file('files'), $user->id);

            // the tenant is out: from now on the case only recovers money
            if ($stage->code === 'eviction' && (int) $legal->case_type === 1) {
                $legal->update(['case_type' => 2]);
                LegalCase::log($legal->id, 'type_changed', 'Unit handed back: ' . LegalCase::TYPES[1] . ' -> ' . LegalCase::TYPES[2] . '.', $user->id);
            }
        });

        LegalMessages::toRoles('legal_stage_changed', ['backoffice_manager'], $legal->tenantContract,
            ['stage' => $stage->name, 'stage_note' => $request->note]);

        return redirect()->route('legal.case', $legal->id)->with('success', 'Stage updated to "' . $stage->name . '".');
    }

    public function commentStore(Request $request, $id)
    {
        $legal = Legal::findOrFail($id);
        $user = \Auth::user();
        abort_unless($user->can('legal_comment_add') && LegalCase::canView($user), 403);
        if ($legal->closed_at) {
            return redirect()->back()->with('error', 'This case is closed.');
        }
        $this->validate($request, ['comment' => 'required|max:2000', 'files.*' => self::FILE_RULE]);

        DB::transaction(function () use ($legal, $request, $user) {
            $updateId = LegalCase::log($legal->id, 'comment', $request->comment, $user->id);
            LegalCase::storeFiles($legal->id, $updateId, $request->file('files'), $user->id);
        });
        return redirect()->route('legal.case', $legal->id)->with('success', 'Comment added.');
    }

    /* ---------------- Settlements ---------------- */

    /** Lawyer records a settlement (or corrects one PLM sent back). Installments add up to the amount. */
    public function settlementStore(Request $request, $id)
    {
        $legal = Legal::findOrFail($id);
        $user = \Auth::user();
        abort_unless($user->can('legal_settlement_enter') && LegalCase::canUpdateStage($user, $legal), 403);

        $current = LegalSettlement::current($legal->id);
        $resubmit = $current && (int) $current->status === LegalSettlement::SENT_BACK;
        if (!$resubmit && !LegalSettlement::canEnter($legal)) {
            return redirect()->route('legal.case', $legal->id)->with('error', 'A settlement is already waiting or running on this case.');
        }
        $this->validateWithBag('modal', $request, [
            'settlement_type' => 'required|in:1,2',
            'agreed_date' => 'required|date|before_or_equal:today',
            'amount' => 'required|numeric|min:0.001',
            'note' => 'required|max:2000',
            'files.*' => self::FILE_RULE,
        ], ['agreed_date.before_or_equal' => 'The agreement / judgment date cannot be in the future.']);
        if ((int) $request->settlement_type === 2 && !LegalSettlement::judgmentReached($legal)) {
            return redirect()->back()->withInput()->with('modal_error', 'A court decided settlement needs the case at Judgment (or later). Update the stage first.');
        }
        $amount = round((float) $request->amount, 3);
        list($rows, $error) = LegalSettlement::cleanInstallments($request->due_date, $request->due_amount, $amount);
        if ($error) {
            return redirect()->back()->withInput()->with('modal_error', $error);
        }

        DB::transaction(function () use ($legal, $request, $user, $amount, $rows, $resubmit, $current) {
            $data = ['settlement_type' => (int) $request->settlement_type, 'agreed_date' => $request->agreed_date, 'amount' => $amount,
                'outstanding_at_entry' => round(LegalCase::outstanding($legal), 3), 'settled_total' => $amount, 'note' => $request->note,
                'status' => LegalSettlement::PENDING, 'updated_at' => now()];
            if ($resubmit) {
                DB::table('legal_settlements')->where('id', $current->id)->update($data + ['sendback_reason' => null]);
                $sid = $current->id;
            } else {
                $sid = DB::table('legal_settlements')->insertGetId($data + ['legal_id' => $legal->id, 'entered_by' => $user->id, 'created_at' => now()]);
            }
            LegalSettlement::saveInstallments($sid, $rows);
            $updateId = LegalCase::log($legal->id, 'settlement', ($resubmit ? 'Settlement corrected and resent' : 'Settlement entered') . ': '
                . LegalSettlement::TYPES[(int) $request->settlement_type] . ', ' . numberFormat($amount) . ' agreed on '
                . date('d/m/Y', strtotime($request->agreed_date)) . ', ' . count($rows) . ' installment' . (count($rows) === 1 ? '' : 's')
                . ".\n" . $request->note, $user->id, ['event_date' => $request->agreed_date]);
            LegalCase::storeFiles($legal->id, $updateId, $request->file('files'), $user->id);
        });
        LegalSettlement::notify($legal, 'legal_settlement_entered', ['amount' => numberFormat($amount)], 'Settlement to verify', false, true);

        return redirect()->route('legal.case', $legal->id)->with('success', 'Settlement sent to PLM for verification.');
    }

    /** PLM verifies (waiver, fees recovered, dates) or sends the settlement back to the lawyer. */
    public function settlementVerify(Request $request, $id)
    {
        $legal = Legal::findOrFail($id);
        $user = \Auth::user();
        abort_unless($user->can('legal_settlement_verify'), 403);
        $settlement = LegalSettlement::current($legal->id);
        if (!$settlement || (int) $settlement->status !== LegalSettlement::PENDING || $legal->closed_at) {
            return redirect()->route('legal.case', $legal->id)->with('error', 'There is no settlement waiting for verification.');
        }
        // the person who recorded a settlement does not verify it (super_admin excepted)
        if ((int) $settlement->entered_by === (int) $user->id && !$user->hasRole('super_admin')) {
            return redirect()->route('legal.case', $legal->id)->with('error', 'You recorded this settlement; another PLM user has to verify it.');
        }

        if ($request->input('decision') === 'sendback') {
            $this->validateWithBag('modal', $request, ['sendback_reason' => 'required|max:1000']);
            DB::table('legal_settlements')->where('id', $settlement->id)->update(['status' => LegalSettlement::SENT_BACK,
                'sendback_reason' => $request->sendback_reason, 'updated_at' => now()]);
            LegalCase::log($legal->id, 'settlement_sent_back', $request->sendback_reason, $user->id);
            LegalSettlement::notify($legal, 'legal_settlement_sent_back', ['reason' => $request->sendback_reason], 'Settlement sent back', true, false);
            return redirect()->route('legal.case', $legal->id)->with('success', 'Settlement sent back to the lawyer.');
        }

        $this->validateWithBag('modal', $request, [
            'waiver_amount' => 'nullable|numeric|min:0',
            'waiver_reason' => 'nullable|max:500',
            'fees_recovered' => 'nullable|numeric|min:0',
        ]);
        $waiver = round((float) $request->waiver_amount, 3);
        if ($waiver > 0 && trim((string) $request->waiver_reason) === '') {
            return redirect()->back()->withInput()->with('modal_error', 'Give the reason for the waiver.');
        }
        $fees = round((float) $request->fees_recovered, 3);
        $total = round((float) $settlement->amount + $fees, 3);
        list($rows, $error) = LegalSettlement::cleanInstallments($request->due_date, $request->due_amount, $total);
        if ($error) {
            return redirect()->back()->withInput()->with('modal_error', $error);
        }

        DB::transaction(function () use ($legal, $settlement, $user, $waiver, $fees, $total, $rows, $request) {
            DB::table('legal_settlements')->where('id', $settlement->id)->update(['status' => LegalSettlement::ACTIVE,
                'waiver_amount' => $waiver, 'waiver_reason' => $waiver > 0 ? $request->waiver_reason : null, 'fees_recovered' => $fees,
                'settled_total' => $total, 'verified_by' => $user->id, 'verified_at' => now(), 'updated_at' => now()]);
            LegalSettlement::saveInstallments($settlement->id, $rows);
            LegalCase::log($legal->id, 'settlement_verified', 'Verified: collect ' . numberFormat($total)
                . ($fees > 0 ? ' (incl. fees ' . numberFormat($fees) . ')' : '')
                . ($waiver > 0 ? ', waiver ' . numberFormat($waiver) . ' - ' . $request->waiver_reason : '') . '.', $user->id);
        });
        // the waiver is a credit on the contract: it leaves every receivable from now on
        \Modules\Masters\Services\LegalWaiver::record(LegalSettlement::current($legal->id), $legal->tenant_contract_id, $user->id);
        LegalSettlement::notify($legal, 'legal_settlement_verified', ['amount' => numberFormat($total)], 'Settlement verified', true, false);
        LegalSettlement::refresh(LegalSettlement::current($legal->id));

        return redirect()->route('legal.case', $legal->id)->with('success', 'Settlement verified.');
    }

    /**
     * Close an active case: "paid in full" needs a completed settlement, "balance written off" a verified
     * one and a reason, "withdrawn" no running settlement and a reason.
     */
    public function close(Request $request, $id)
    {
        $legal = Legal::findOrFail($id);
        $user = \Auth::user();
        abort_unless($user->can('legal_case_close'), 403);
        if (!LegalCase::isActive($legal)) {
            return redirect()->route('legal.case', $legal->id)->with('error', 'Only an active case can be closed here.');
        }
        $this->validateWithBag('modal', $request, ['close_reason' => 'required|in:paid_in_full,written_off,withdrawn', 'close_note' => 'nullable|max:1000']);
        $reason = $request->close_reason;
        $settlement = LegalSettlement::current($legal->id);
        $status = $settlement ? (int) $settlement->status : null;
        $error = null;
        if ($reason === 'paid_in_full' && $status !== LegalSettlement::COMPLETED) {
            $error = 'Paid in full needs a verified settlement that is fully collected.';
        } elseif ($reason === 'written_off' && !in_array($status, [LegalSettlement::ACTIVE, LegalSettlement::BROKEN, LegalSettlement::COMPLETED], true)) {
            $error = 'Writing off the balance needs a verified settlement.';
        } elseif ($reason === 'withdrawn' && in_array($status, [LegalSettlement::PENDING, LegalSettlement::ACTIVE, LegalSettlement::COMPLETED], true)) {
            $error = 'This case has a settlement; close it as paid in full or balance written off.';
        }
        if (!$error && $reason !== 'paid_in_full' && trim((string) $request->close_note) === '') {
            $error = 'Give the reason for closing.';
        }
        if ($error) {
            return redirect()->back()->withInput()->with('modal_error', $error);
        }

        $writeOff = $reason === 'written_off' ? LegalSettlement::writeOffAmount($settlement, $legal) : 0.0;
        DB::transaction(function () use ($legal, $reason, $request, $user, $settlement, $writeOff) {
            $this->closeRecords($legal, $reason, $request->close_note, $user->id);
            // the unpaid balance becomes a waiver, so it does not show as owed again (user, 18/10/2026)
            if ($writeOff > 0) {
                LegalWaiver::writeOff($settlement, $legal->tenant_contract_id, $writeOff, $request->close_note, $user->id);
                LegalCase::log($legal->id, 'settlement', 'Balance of ' . numberFormat($writeOff) . ' written off (recorded as a waiver).', $user->id);
            }
        });
        // ARE, PLM and the case's lawyer hear about the close (it used to be ARE only)
        LegalMessages::toRoles('legal_case_closed', ['are', 'backoffice_manager'], $legal->tenantContract);
        if ($legal->lawyer_user_id && ($lawyer = User::find($legal->lawyer_user_id))) {
            LegalMessages::toUsers('legal_case_closed', [$lawyer], $legal->tenantContract);
        }

        return redirect()->route('legal.case', $legal->id)->with('success', 'Case closed: ' . LegalCase::reasonLabel($reason) . '.');
    }

    /** What closing a case changes: close date/reason + timeline, final stage, contract out of legal, process users done. */
    private function closeRecords(Legal $legal, $reason, $note, $userId)
    {
        $closedStage = DB::table('legal_stages')->where('is_final', true)->orderBy('sort_order')->value('id');
        LegalCase::close($legal, $reason, $note, $userId);
        $legal->update(['stage_id' => $closedStage ?: $legal->stage_id, 'legal_is_closed' => 1]);
        DB::table('tenant_contracts')->where('id', $legal->tenant_contract_id)->update(['status' => 0]);
        $legal->legalUser()->update(['status' => 0]);
    }

    /**
     * Bulk close from Active cases (user, 20/10/2026): old cases already finished outside the system.
     * Reason Closed or Withdrawn with a note; the same rules as a single close otherwise - only active
     * cases handled by Al Habib, none with a settlement in progress (those close from the case page as
     * paid in full / written off). No messages are sent.
     */
    public function bulkClose(Request $request)
    {
        $user = \Auth::user();
        abort_unless($user->can('legal_case_close'), 403);
        $this->validate($request, [
            'close_reason' => 'required|in:closed,withdrawn',
            'close_note'   => 'required|max:1000',
            'ids'          => 'required|array|max:5000',
            'ids.*'        => 'integer',
        ], ['ids.required' => 'Select at least one case.', 'close_note.required' => 'Give the reason for closing.']);

        $ids = array_values(array_unique(array_map('intval', $request->ids)));
        $note = trim($request->close_note) . ' (bulk close, Active cases)';
        $closed = 0;
        $withSettlement = 0;
        foreach (Legal::whereIn('id', $ids)->get() as $legal) {
            if (!LegalCase::isActive($legal)) {
                continue;
            }
            $settlement = LegalSettlement::current($legal->id);
            if ($settlement && in_array((int) $settlement->status, [LegalSettlement::PENDING, LegalSettlement::ACTIVE, LegalSettlement::COMPLETED], true)) {
                $withSettlement++;
                continue;
            }
            DB::transaction(function () use ($legal, $request, $note, $user) {
                $this->closeRecords($legal, $request->close_reason, $note, $user->id);
            });
            $closed++;
        }

        $skipped = count($ids) - $closed;
        $msg = $closed . ' case' . ($closed == 1 ? '' : 's') . ' closed (' . LegalCase::reasonLabel($request->close_reason) . ').';
        if ($skipped > 0) {
            $msg .= ' ' . $skipped . ' skipped' . ($withSettlement ? ' (' . $withSettlement . ' with a settlement - close those from the case page)' : '')
                . ($skipped > $withSettlement ? ($withSettlement ? ',' : '') . ' (no longer active or not handled by Al Habib)' : '') . '.';
        }
        return redirect()->back()->with($closed ? 'success' : 'error', $msg);
    }

    /** Reopen a closed case as a new case linked to it (e.g. a settlement cheque bounced). */
    public function reopen(Request $request, $id)
    {
        $old = Legal::findOrFail($id);
        $user = \Auth::user();
        abort_unless($user->can('legal_settlement_verify') || $user->can('plms_approve'), 403);
        if (!$old->closed_at || $old->close_reason === 'handed_over') {
            return redirect()->route('legal.case', $old->id)->with('error', 'Only a case closed on our side can be reopened.');
        }
        if (LegalCase::openCaseFor($old->tenant_contract_id)) {
            return redirect()->route('legal.case', $old->id)->with('error', 'The contract already has an open legal case.');
        }
        $this->validateWithBag('modal', $request, ['reopen_reason' => 'required|max:1000']);
        $contract = $old->tenantContract;

        $new = DB::transaction(function () use ($old, $contract, $request, $user) {
            DB::table('tenant_contracts')->where('id', $contract->id)->update(['status' => 5]);
            list($new) = LegalCase::open($contract->fresh(), 'Reopened: ' . $request->reopen_reason, $user->id);
            $new->update(['case_type' => $old->case_type, 'handled_by' => $old->handled_by, 'priority' => $old->priority,
                'lawyer_user_id' => $old->lawyer_user_id, 'court_case_no' => $old->court_case_no, 'court_name' => $old->court_name]);
            LegalCase::log($old->id, 'reopened', 'Reopened as case #' . $new->id . ': ' . $request->reopen_reason, $user->id);
            if ($request->waiver_no_longer_applies
                && \Modules\Masters\Services\LegalWaiver::cancel($old->id, null, 'Reopened as case #' . $new->id)) {
                LegalCase::log($old->id, 'settlement', 'Waiver no longer applies (tenant broke the settlement); the amount is owed again.', $user->id);
            }
            $this->assignPlm($new, $user);
            return $new;
        });
        LegalMessages::toRoles('legal_case_reopened', ['backoffice_manager'], $contract, ['reason' => $request->reopen_reason]);

        return redirect(LegalCase::screenUrl($new))->with('success', 'Case reopened as #' . $new->id . '; it waits for PLM approval.');
    }

    /** Attach the PLM approval (801) users, the same way Move to Legal does. */
    private function assignPlm(Legal $legal, $user)
    {
        $general = new General;
        $assign = $general->roleUsersFromProcess(801, $location_id = null, $pricerange_id = null, $tenant_status = null);
        if ($assign != false) {
            foreach ($assign->assign as $val) {
                $legal->legalUsers()->attach($val->role_id, ['user_id' => $val->user_id]);
            }
        } else {
            $process = $general->workFlowProcess(801);
            if ($process && DB::table('users')->where('id', $process->default_user_id)->exists()) {
                $legal->legalUsers()->attach($process->default_role, ['user_id' => $process->default_user_id]);
            }
        }
        $legal->legalUsers()->attach($user->default_role, ['user_id' => $user->id]);
    }

    /* ---------------- Legal Stages master ---------------- */

    public function stages()
    {
        abort_unless(\Auth::user()->can('legal_stages_master'), 403);
        $stages = DB::table('legal_stages as s')
            ->leftJoin(DB::raw('(SELECT stage_id, count(*) AS n FROM legal WHERE closed_at IS NULL GROUP BY stage_id) c'), 'c.stage_id', '=', 's.id')
            ->select('s.*', DB::raw('coalesce(c.n, 0) as open_cases'))
            ->orderBy('s.sort_order')->orderBy('s.id')->get();
        return view('masters::Legal.stages', compact('stages'));
    }

    public function stageMasterStore(Request $request)
    {
        abort_unless(\Auth::user()->can('legal_stages_master'), 403);
        $this->validate($request, [
            'id' => 'nullable|integer',
            'name' => 'required|max:100',
            'applies_to' => 'required|in:0,1,2',
            'status' => 'nullable|in:0,1',
        ]);
        $name = trim($request->name);
        $dup = DB::table('legal_stages')->whereRaw('lower(name) = ?', [mb_strtolower($name)])
            ->when($request->id, function ($q) use ($request) { $q->where('id', '<>', (int) $request->id); })->exists();
        if ($dup) {
            return redirect()->route('legal.stages')->with('error', 'A stage named "' . $name . '" already exists.');
        }
        $now = now();
        if ($request->id) {
            $stage = DB::table('legal_stages')->where('id', (int) $request->id)->first();
            abort_unless($stage, 404);
            $data = ['name' => $name, 'applies_to' => (int) $request->applies_to, 'updated_at' => $now];
            if (!$stage->is_final) {
                $data['status'] = (int) $request->input('status', 1);
            }
            DB::table('legal_stages')->where('id', $stage->id)->update($data);
        } else {
            // new stages go before the final "Closed" stage
            $final = DB::table('legal_stages')->where('is_final', true)->orderBy('sort_order')->first();
            $order = $final ? (int) $final->sort_order : (int) DB::table('legal_stages')->max('sort_order') + 1;
            DB::table('legal_stages')->where('sort_order', '>=', $order)->increment('sort_order');
            DB::table('legal_stages')->insert(['name' => $name, 'applies_to' => (int) $request->applies_to, 'is_final' => false,
                'sort_order' => $order, 'status' => 1, 'created_by' => \Auth::user()->id, 'created_at' => $now, 'updated_at' => $now]);
        }
        return redirect()->route('legal.stages')->with('success', 'Stage saved.');
    }

    /** Move a stage one place up or down (the final stage stays last). */
    public function stageMove(Request $request, $id)
    {
        abort_unless(\Auth::user()->can('legal_stages_master'), 403);
        $stage = DB::table('legal_stages')->where('id', (int) $id)->first();
        abort_unless($stage && !$stage->is_final, 404);
        $up = $request->input('dir') === 'up';
        $other = DB::table('legal_stages')->where('is_final', false)
            ->where('sort_order', $up ? '<' : '>', $stage->sort_order)
            ->orderBy('sort_order', $up ? 'desc' : 'asc')->first();
        if ($other) {
            DB::transaction(function () use ($stage, $other) {
                DB::table('legal_stages')->where('id', $stage->id)->update(['sort_order' => $other->sort_order]);
                DB::table('legal_stages')->where('id', $other->id)->update(['sort_order' => $stage->sort_order]);
            });
        }
        return redirect()->route('legal.stages');
    }
}
