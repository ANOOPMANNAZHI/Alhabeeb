<?php

namespace Modules\Masters\Http\Controllers;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\BackOffice\Services\LegalMessages;
use Modules\Masters\Entities\Legal;
use Modules\Masters\Services\LegalCase;

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
        clearNotification('Modules\Masters\Notifications\LegalNotification', $legal->id);

        $user = \Auth::user();
        return view('masters::Legal.case_page', [
            'legal' => $legal,
            'timeline' => LegalCase::timeline($legal->id),
            'docs' => LegalCase::documentsByUpdate($legal->id),
            'outstanding' => LegalCase::outstanding($legal),
            'defaultHandledBy' => LegalCase::defaultHandledBy($legal->building_id),
            'stages' => LegalCase::stagesFor($legal),
            'current' => LegalCase::stage($legal->stage_id),
            'canStage' => LegalCase::isActive($legal) && LegalCase::canUpdateStage($user, $legal),
            'canComment' => !$legal->closed_at && $user->can('legal_comment_add'),
            'related' => Legal::where('tenant_contract_id', $legal->tenant_contract_id)->where('id', '<>', $legal->id)->orderBy('id')->get(),
        ]);
    }

    public function stageStore(Request $request, $id)
    {
        $legal = Legal::findOrFail($id);
        $user = \Auth::user();
        abort_unless(LegalCase::canUpdateStage($user, $legal), 403);

        $stage = LegalCase::stage((int) $request->stage_id);
        if ($error = LegalCase::stageError($legal, $stage)) {
            return redirect()->back()->withInput()->with('error', $error);
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
        $this->validate($request, $rules, [
            'event_date.before_or_equal' => 'The stage date cannot be in the future.',
            'next_hearing_date.after_or_equal' => 'The next hearing date cannot be in the past.',
        ]);

        $details = [];
        $changes = ['stage_id' => $stage->id, 'next_hearing_date' => $request->next_hearing_date ?: null];
        if ($stage->code === 'filed') {
            $claim = $request->claim_amount !== null && $request->claim_amount !== '' ? round((float) $request->claim_amount, 3) : LegalCase::outstanding($legal);
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
