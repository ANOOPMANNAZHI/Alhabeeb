<?php

namespace Modules\BackOffice\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\BackOffice\Entities\Pdc;
use Modules\BackOffice\Exports\PdcBankSheetResultExport;
use Modules\BackOffice\Entities\ReceiptsGeneration;
use Modules\BackOffice\Services\BankClearingCodes;
use Modules\BackOffice\Services\BankSheetPdcMatcher;
use Modules\BackOffice\Services\BankSheetPostedCheques;
use Modules\BackOffice\Services\BankSheetRejectedCheques;
use Modules\BackOffice\Services\BankSheetReader;
use Modules\BackOffice\Services\BankSheetResultRows;
use Modules\BackOffice\Exports\PdcBankSheetResultSheet;
use Modules\BackOffice\Services\VatReceiptCreator;
use Modules\Masters\Entities\Bank;
use Modules\Sales\Entities\TenantContract;

/**
 * PDC posting from the bank's cheque-clearing sheet: upload the export,
 * review which unposted PDCs it matches (pick between candidates when a
 * cheque number is shared), then post the chosen ones through the same
 * code as the manual PDC posting screen. The preview is a GET on the
 * stored file so it can be paged and revisited; the user's unticks and
 * candidate choices live in the session keyed by that file, and the
 * match is re-derived from the file on post so nothing is posted from
 * stale page state. Runs on PHP < 7.4 in production: no arrow functions.
 */
class PdcBankSheetController extends Controller
{
    const STORE_DIR = 'pdc_bank_sheets';
    const PER_PAGE  = 50;
    /** bank.bank_code pre-selected as the deposit bank on the review page. */
    const DEFAULT_DEPOSIT_BANK_CODE = 'BMI';

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:pdc_posting_list');
    }

    /** Step 1: store the uploaded sheet and go to its preview. */
    public function upload(Request $request)
    {
        $this->validate($request, [
            'bank_sheet' => 'required|file|mimes:xls,xlsx',
        ]);

        $upload = $request->file('bank_sheet');
        $stored = date('Ymd_His') . '_' . \Auth::user()->id . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $upload->getClientOriginalName());
        $upload->storeAs(self::STORE_DIR, $stored);

        try {
            $this->analyse($stored); // fail fast on an unreadable sheet
        } catch (\RuntimeException $e) {
            return redirect()->route('pdcPosting')->with('error', $e->getMessage());
        }

        $request->session()->forget($this->selectionKey($stored));

        return redirect()->route('pdcBankSheetPreview', $stored);
    }

    /** Step 2: the paged preview (ambiguous first, then matched, then cannot-post). */
    public function preview(Request $request, $stored)
    {
        try {
            $analysis = $this->analyse($stored);
        } catch (\RuntimeException $e) {
            return redirect()->route('pdcPosting')->with('error', $e->getMessage());
        }

        $selection = $this->selection($request, $stored);
        $sections  = $analysis['sections'];

        // per-column filters, one set per section: ?a[cheque]=..&m[tenant]=..&c[status]=..
        $filters = [];
        foreach (['ambiguous' => 'a', 'matched' => 'm', 'cannot' => 'c'] as $k => $prefix) {
            $filters[$k] = $this->cleanFilters($request->input($prefix, []));
            if (count($filters[$k]) > 0) {
                $sections[$k] = $this->filterRows($sections[$k], $filters[$k]);
            }
        }
        $shown = [
            'ambiguous' => count($sections['ambiguous']),
            'matched'   => count($sections['matched']),
            'cannot'    => count($sections['cannot']),
        ];

        $matched = $this->paginate($sections['matched'], $request, 'mpage', $stored, 'matched');
        $cannot  = $this->paginate($sections['cannot'], $request, 'cpage', $stored, 'cannot');
        $posted  = $this->paginate($sections['posted'], $request, 'ppage', $stored, 'posted');

        // Which result tab opens: ?tab=, else the tab being paged / filtered, else Auto-matched
        $activeTab = in_array($request->input('tab'), ['matched', 'cannot', 'posted', 'rejected'], true) ? $request->input('tab')
            : ($request->has('ppage') ? 'posted'
            : (($request->has('cpage') || count($filters['cannot']) > 0) ? 'cannot' : 'matched'));

        $chosen = 0;
        foreach ($selection['choice'] as $v) {
            if ($v !== '') {
                $chosen++;
            }
        }
        $toPost = $analysis['counts']['matched'] - count($selection['unticked']) + $chosen;

        $vatRowNos = [];
        foreach ($sections['vat'] as $r) {
            $vatRowNos[$r['row']['row_no']] = true;
        }
        $toPost += count(array_intersect_key($selection['vat'], $vatRowNos));

        $banks       = Bank::active()->where('accounts_bank', 1)->orderBy('bank_name', 'ASC')->get();
        $defaultBank = $banks->first(function ($b) { return trim($b->bank_code) === self::DEFAULT_DEPOSIT_BANK_CODE; });
        // deposit date defaults to the day the bank scanned the batch, never later than today
        $defaultDate = $analysis['batch_date'] && $analysis['batch_date'] <= date('Y-m-d') ? $analysis['batch_date'] : date('Y-m-d');

        // Cheques the bank rejected, matched to their PDCs so they can be marked bounced here
        $rejectedRows = [];
        foreach ($analysis['results'] as $r) {
            if ($r['status'] === BankSheetPdcMatcher::REJECTED) {
                $rejectedRows[] = $r['row'];
            }
        }
        $rejected = $rejectedRows
            ? BankSheetRejectedCheques::match($rejectedRows, $this->candidatePdcs($rejectedRows, true), $this->bankMap())
            : [];

        // codes on the sheet that no PLMS bank carries yet: can be added here (Bank master edit right)
        $unknownCodes = BankClearingCodes::unknown($analysis['results']);
        $codeBanks = $unknownCodes ? Bank::active()->orderBy('bank_name', 'ASC')->get(['id', 'bank_name', 'bank_code', 'bank_clearing_codes']) : collect();

        return view('backoffice::Routine.pdc_bank_sheet_preview', [
            'progressUrl'   => route('pdcBankSheetProgress', [$stored, PdcBankSheetProgressController::key($stored)]),
            'unknownCodes'  => $unknownCodes,
            'codeBanks'     => $codeBanks,
            'canEditBank'   => \Auth::user()->can('edit_bank'),
            'rejected'      => $rejected,
            'activeTab'     => $activeTab,
            'stored'        => $stored,
            'fileName'      => $this->originalName($stored),
            'banks'         => $banks,
            'defaultBankId' => $defaultBank ? $defaultBank->id : null,
            'ambiguous' => $sections['ambiguous'],
            'vatRows'   => $sections['vat'],
            'matched'   => $matched,
            'cannot'    => $cannot,
            'posted'    => $posted,
            'counts'    => $analysis['counts'],
            'selection' => $selection,
            'toPost'    => $toPost,
            'today'         => date('Y-m-d'),
            'defaultDate'   => $defaultDate,
            'filters'       => $filters,
            'shown'         => $shown,
        ]);
    }

    /** Non-empty, trimmed string filters only. */
    private function cleanFilters($input)
    {
        $out = [];
        foreach ((array) $input as $field => $value) {
            $value = trim((string) $value);
            if ($value !== '' && preg_match('/^[a-z_]+$/', (string) $field)) {
                $out[$field] = $value;
            }
        }
        return $out;
    }

    /**
     * Rows matching every column filter (case-insensitive "contains";
     * status is an exact key). Column => what it searches:
     *   cheque  sheet cheque no (with or without leading zeros)
     *   date    sheet cheque date as d/m/Y or Y-m-d
     *   amount  sheet amount
     *   bank    sheet bank name
     *   status  result status key (cannot-post table)
     *   why     reason text
     *   tenant / agr / building / type / pbank / plms (all of them)
     *           matched rows, any candidate group, or a cannot-post row's
     *           related (reference) PDCs, of the PDC side
     */
    private function filterRows(array $rows, array $filters)
    {
        $out = [];
        foreach ($rows as $r) {
            // cannot-post rows have neither; their reference PDCs ('related') are searched instead
            $groups = $r['pdcs'] ? [$r['pdcs']] : ($r['candidates'] ? $r['candidates'] : [$r['related']]);
            $pdcs   = [];
            foreach ($groups as $g) {
                foreach ($g as $p) {
                    $pdcs[] = $p;
                }
            }
            $ok = true;
            foreach ($filters as $field => $needle) {
                switch ($field) {
                    case 'cheque':
                        $hay = [$r['row']['cheque_no'], BankSheetPdcMatcher::normaliseChequeNo($r['row']['cheque_no'])];
                        break;
                    case 'date':
                        $d   = $r['row']['cheque_date'];
                        $hay = [$d, $d ? date('d/m/Y', strtotime($d)) : ''];
                        break;
                    case 'amount':
                        $hay = [(string) $r['row']['amount'], number_format((float) $r['row']['amount'], 3, '.', ',')];
                        break;
                    case 'bank':
                        $hay = [$r['row']['bank_name']];
                        break;
                    case 'status':
                        if ($r['status'] !== $needle) {
                            $ok = false;
                            break 2;
                        }
                        continue 2;
                    case 'why':
                        $hay = [$r['reason']];
                        break;
                    case 'tenant':
                        $hay = array_column($pdcs, 'tenant_name');
                        break;
                    case 'agr':
                        $hay = array_column($pdcs, 'tenant_contract_no');
                        break;
                    case 'building':
                        $hay = array_merge(array_column($pdcs, 'building_name'), array_column($pdcs, 'unit_no'));
                        break;
                    case 'type':
                        $hay = array_column($pdcs, 'pdc_type_name');
                        break;
                    case 'pbank':
                        $hay = array_column($pdcs, 'bank_name');
                        break;
                    case 'plms': // whole PDC side in one box (ambiguous table)
                        $hay = array_merge(
                            array_column($pdcs, 'tenant_name'),
                            array_column($pdcs, 'tenant_contract_no'),
                            array_column($pdcs, 'building_name'),
                            array_column($pdcs, 'unit_no'),
                            array_column($pdcs, 'bank_name')
                        );
                        break;
                    default:
                        continue 2;
                }
                $hay = array_filter($hay, function ($v) { return $v !== null && $v !== ''; });
                if (mb_stripos(implode("\n", $hay), $needle) === false) {
                    $ok = false;
                    break;
                }
            }
            if ($ok) {
                $out[] = $r;
            }
        }
        return $out;
    }

    /** AJAX: remember an untick / re-tick of a matched row or a candidate choice for an ambiguous one. */
    public function select(Request $request, $stored)
    {
        $this->validate($request, [
            'row'   => 'required|integer',
            'kind'  => 'required|in:tick,choice,vat',
            'value' => 'present',
        ]);

        $selection = $this->selection($request, $stored);
        $row = (int) $request->row;

        if ($request->kind === 'tick') {
            if ($request->value === '1' || $request->value === 1 || $request->value === true) {
                unset($selection['unticked'][$row]);
            } else {
                $selection['unticked'][$row] = true;
            }
        } elseif ($request->kind === 'vat') {
            if ($request->value === '1' || $request->value === 1 || $request->value === true) {
                $selection['vat'][$row] = true;
            } else {
                unset($selection['vat'][$row]);
            }
        } else {
            $selection['choice'][$row] = (string) $request->value;
        }

        $request->session()->put($this->selectionKey($stored), $selection);

        return response()->json(['ok' => true]);
    }

    /** Step 3: post the ticked matched rows and the chosen ambiguous candidates. */
    public function post(Request $request, $stored)
    {
        $this->validate($request, [
            'pdc_deposit_date' => 'required|date|before_or_equal:today',
            'bank_id'          => 'required|exists:bank,id',
        ]);

        // A big sheet can run for minutes: finish the run even if the browser stops waiting
        @set_time_limit(0);
        ignore_user_abort(true);

        try {
            $analysis = $this->analyse($stored);
        } catch (\RuntimeException $e) {
            return $this->postFailed($request, route('pdcPosting'), $e->getMessage());
        }

        $selection = $this->selection($request, $stored);
        Cache::forget(PdcBankSheetProgressController::cacheKey($stored));

        $toPost    = []; // sheet row_no => [pdc rows]
        $vatByDiff = []; // row_no => VAT amount (Case B)
        $chargeIds = []; // row_no => ids of "Others" PDCs that are charges (E&W ...), not VAT
        $sheetAccounts = []; // row_no => the sheet's Pay Account, saved onto posted PDCs that have none
        foreach ($analysis['results'] as $r) {
            $rowNo = $r['row']['row_no'];
            $sheetAccounts[$rowNo] = isset($r['row']['account_no']) ? $r['row']['account_no'] : null;
            if ($r['status'] === BankSheetPdcMatcher::MATCHED && !isset($selection['unticked'][$rowNo])) {
                $toPost[$rowNo] = $r['pdcs'];
                if (!empty($r['charge_ids'])) {
                    $chargeIds[$rowNo] = $r['charge_ids'];
                }
            } elseif ($r['status'] === BankSheetPdcMatcher::AMBIGUOUS && !empty($selection['choice'][$rowNo])) {
                $group = $this->candidateByIds($r['candidates'], $selection['choice'][$rowNo]);
                if ($group !== null) {
                    $toPost[$rowNo] = $group;
                }
            } elseif ($r['status'] === BankSheetPdcMatcher::VAT_CHECK && !empty($selection['vat'][$rowNo])) {
                $toPost[$rowNo] = $r['pdcs'];
                $vatByDiff[$rowNo] = $r['vat_amount'];
            }
        }

        if (count($toPost) === 0) {
            return $this->postFailed($request, route('pdcBankSheetPreview', $stored), 'Nothing selected to post');
        }

        try {
            return $this->postRows($request, $stored, $analysis, $toPost, $vatByDiff, $sheetAccounts, $chargeIds);
        } catch (\Throwable $e) {
            \Log::error('PDC bank sheet post failed: ' . $e->getMessage(), ['stored' => $stored, 'exception' => $e]);
            return $this->postFailed($request, route('pdcBankSheetPreview', $stored),
                'Posting stopped with an error: ' . $e->getMessage() . '. Cheques finished before the error stay posted - check Posting history before posting again.');
        }
    }

    /**
     * Post each chosen cheque in its own transaction; a cheque that fails is
     * recorded and the rest carry on.
     */
    private function postRows(Request $request, $stored, array $analysis, array $toPost, array $vatByDiff, array $sheetAccounts, array $chargeIds = [])
    {
        $routines = app(RoutinesController::class);
        $posted   = 0;
        $pdcsDone = 0;
        $tillEnd  = [];
        $failed   = [];
        $outcome  = []; // row_no => [status, reason, pdc_ids, vat_receipt_no]

        $vatCreator = new VatReceiptCreator($routines);
        $vatMade    = 0;
        $vatReusedCount = 0;
        $total = count($toPost);
        $done  = 0;
        $this->progress($stored, 0, $total, 0, 0);
        foreach ($toPost as $rowNo => $pdcs) {
            $this->progress($stored, $done, $total, $posted, count($failed), $pdcs[0]->pdc_check_no);
            $done++;
            $ids = [];
            $vatNos = [];
            $vatReused = [];
            $vatBefore = $vatMade; // the closure counts receipts; undo if the cheque rolls back
            $vatReusedBefore = $vatReusedCount;
            try {
                $charges = isset($chargeIds[$rowNo]) ? array_flip($chargeIds[$rowNo]) : [];
                DB::transaction(function () use ($routines, $vatCreator, $pdcs, $request, $rowNo, $vatByDiff, $sheetAccounts, $charges, &$ids, &$tillEnd, &$vatNos, &$vatMade, &$vatReused, &$vatReusedCount) {
                    $rent = [];
                    $vat  = [];
                    $other = []; // "Others" that are charges (E&W ...): a general receipt each, as on the PDC Posting screen
                    foreach ($pdcs as $p) {
                        $pdc = Pdc::lockForUpdate()->find($p->id);
                        if ($pdc === null || (int) $pdc->pdc_is_posted === 1) {
                            throw new \RuntimeException('cheque ' . $p->pdc_check_no . ' was already posted');
                        }
                        if (isset($charges[(int) $pdc->id])) {
                            $other[] = $pdc;
                        } elseif (BankSheetPdcMatcher::isVat($pdc)) {
                            $vat[] = $pdc;
                        } else {
                            $rent[] = $pdc;
                        }
                    }
                    $hasVat = count($vat) > 0 || isset($vatByDiff[$rowNo]);

                    $periods = []; // contract id => [from, to] of the rent receipts just made
                    foreach ($rent as $pdc) {
                        $result = $routines->postSinglePdc($pdc, $request->pdc_deposit_date, $request->bank_id);
                        if (!$result['posted']) {
                            if ($hasVat) {
                                throw new \RuntimeException('receipts already run to contract end for ' . $result['contract_no'] . '; VAT not posted');
                            }
                            $tillEnd[] = $result['contract_no'];
                            continue;
                        }
                        $ids[] = $pdc->id;
                        $receipt = ReceiptsGeneration::where('receipts_generation_receipt_no', Pdc::where('id', $pdc->id)->value('pdc_receipt_no'))->first();
                        $cid = (int) $pdc->tenant_contract_id;
                        $from = $receipt ? $receipt->receipts_generation_eff_from : null;
                        $to   = $receipt ? $receipt->receipts_generation_eff_to : null;
                        if (!isset($periods[$cid])) {
                            $periods[$cid] = [$from, $to];
                        } else {
                            if ($from && (!$periods[$cid][0] || $from < $periods[$cid][0])) { $periods[$cid][0] = $from; }
                            if ($to && (!$periods[$cid][1] || $to > $periods[$cid][1])) { $periods[$cid][1] = $to; }
                        }
                    }
                    foreach ($other as $pdc) {
                        if (count($rent) > 0 && count($ids) === 0) {
                            throw new \RuntimeException('receipts already run to contract end; ' . ($pdc->pdc_remark ?: 'other charge') . ' not posted');
                        }
                        $routines->postSinglePdc($pdc, $request->pdc_deposit_date, $request->bank_id);
                        $ids[] = $pdc->id;
                    }
                    $this->fillAccountNo($ids, $sheetAccounts[$rowNo]);
                    if (!$hasVat) {
                        return;
                    }

                    $chequeNo = $rent[0]->pdc_check_no;
                    $bankCode = Bank::where('id', $rent[0]->bank_id)->value('bank_code');
                    // Case B: one receipt for the actual extra; Case A: one per contract, summing its VAT PDCs
                    if (isset($vatByDiff[$rowNo])) {
                        $jobs = [['contract_id' => (int) $rent[0]->tenant_contract_id, 'amount' => $vatByDiff[$rowNo], 'pdcs' => $vat]];
                    } else {
                        $byContract = [];
                        foreach ($vat as $v) {
                            $cid = (int) $v->tenant_contract_id;
                            if (!isset($byContract[$cid])) {
                                $byContract[$cid] = ['contract_id' => $cid, 'amount' => 0.0, 'pdcs' => []];
                            }
                            $byContract[$cid]['amount'] += (float) $v->pdc_amt;
                            $byContract[$cid]['pdcs'][] = $v;
                        }
                        $jobs = [];
                        foreach ($byContract as $job) {
                            $job['amount'] = round($job['amount'], 3);
                            $jobs[] = $job;
                        }
                    }

                    foreach ($jobs as $job) {
                        if (!isset($periods[$job['contract_id']]) || !$periods[$job['contract_id']][0]) {
                            throw new \RuntimeException('no rent receipt period for the VAT of cheque ' . $chequeNo);
                        }
                        $made = $vatCreator->create([
                            'contract'        => TenantContract::with(['Unit', 'building'])->find($job['contract_id']),
                            'cheque_no'       => $chequeNo,
                            'bank_code'       => $bankCode,
                            'amount'          => $job['amount'],
                            'eff_from'        => $periods[$job['contract_id']][0],
                            'eff_to'          => $periods[$job['contract_id']][1],
                            'deposit_date'    => $request->pdc_deposit_date,
                            'deposit_bank_id' => $request->bank_id,
                            'vat_pdcs'        => $job['pdcs'],
                        ]);
                        $vatNos[] = $made['receipt_no'];
                        if ($made['created']) {
                            $vatMade++;
                        } else {
                            $vatReused[] = $made['receipt_no'];
                            $vatReusedCount++;
                        }
                        foreach ($job['pdcs'] as $v) {
                            $ids[] = $v->id;
                        }
                    }
                    $this->fillAccountNo($ids, $sheetAccounts[$rowNo]); // the VAT PDCs just closed
                });
                $posted++;
                $pdcsDone += count($ids);
                $reused = count($vatReused) ? 'VAT receipt already exists: ' . implode(', ', $vatReused) : null;
                $outcome[$rowNo] = ['posted', $reused, $ids, implode(',', $vatNos)];
            } catch (\Throwable $e) {
                $vatMade = $vatBefore;
                $vatReusedCount = $vatReusedBefore;
                $failed[] = $pdcs[0]->pdc_check_no . ': ' . $e->getMessage();
                $outcome[$rowNo] = ['failed', $e->getMessage(), [], null];
            }
        }

        $this->progress($stored, $total, $total, $posted, count($failed), null, 'Saving the posting history…');
        $uploadId = $this->audit($request, $stored, $analysis['results'], $outcome, $posted, $pdcsDone);
        $this->progress($stored, $total, $total, $posted, count($failed), null, 'Done', true);
        $request->session()->forget($this->selectionKey($stored));

        $msg = $posted . ' cheque(s) posted (' . $pdcsDone . ' PDC row(s)' . ($vatMade > 0 ? ', ' . $vatMade . ' VAT receipt(s)' : '') . ($vatReusedCount > 0 ? ', ' . $vatReusedCount . ' existing VAT receipt(s) reused' : '') . '); '
             . ($analysis['counts']['cannot']) . ' left for manual posting';
        if (count($tillEnd) > 0) {
            $msg .= '. Receipts already run to contract end for: ' . implode(', ', array_unique($tillEnd));
        }
        $request->session()->flash('success', $msg);
        if (count($failed) > 0) {
            $request->session()->flash('error', 'Failed: ' . implode(' | ', $failed));
        }
        $url = route('pdcBankSheetResult', $uploadId);
        return $request->ajax() ? response()->json(['ok' => true, 'redirect' => $url]) : redirect($url);
    }

    /** Where a running post is (cheque n of total), for the progress overlay on the review page. */
    private function progress($stored, $done, $total, $posted, $failed, $chequeNo = null, $msg = null, $finished = false)
    {
        Cache::put(PdcBankSheetProgressController::cacheKey($stored), [
            'done' => $done, 'total' => $total, 'posted' => $posted, 'failed' => $failed, 'finished' => $finished,
            'msg'  => $msg ?: ($chequeNo !== null ? 'Posting cheque ' . ($done + 1) . ' of ' . $total . ' (no. ' . $chequeNo . ')' : 'Starting…'),
        ], 30); // minutes (Laravel 5.7)
    }

    /** Error answer for post(): JSON for the review page's AJAX call, else a redirect carrying the message. */
    private function postFailed(Request $request, $url, $message)
    {
        if ($request->ajax()) {
            return response()->json(['ok' => false, 'message' => $message], 422);
        }
        return redirect($url)->with('error', $message);
    }

    /**
     * Posting history, one block per posting date (newest first): the day's
     * totals and its uploads. Optional from/to filter on the posting date.
     */
    public function uploads(Request $request)
    {
        $from = $this->validDate($request->input('from'));
        $to   = $this->validDate($request->input('to'));

        $days = DB::table('pdc_bank_sheet_uploads')
            ->selectRaw('created_at::date AS day, COUNT(*) AS uploads, SUM(rows_posted) AS cheques, SUM(pdcs_posted) AS pdcs')
            ->when($from, function ($q) use ($from) { $q->whereRaw('created_at::date >= ?', [$from]); })
            ->when($to, function ($q) use ($to) { $q->whereRaw('created_at::date <= ?', [$to]); })
            ->groupBy(DB::raw('created_at::date'))
            ->orderBy('day', 'desc')
            ->paginate(15)
            ->appends(['from' => $from, 'to' => $to]);

        $uploadsByDay = [];
        $totalsByDay  = [];
        $dayList = [];
        foreach ($days as $d) {
            $dayList[] = $d->day;
            $uploadsByDay[$d->day] = [];
            $totalsByDay[$d->day]  = ['receipts' => 0, 'amount' => 0];
        }

        if (count($dayList) > 0) {
            $uploads = $this->uploadQuery()
                ->whereIn(DB::raw('u.created_at::date'), $dayList)
                ->orderBy('u.id', 'desc')
                ->get();
            foreach ($uploads as $u) {
                $u->original_name = $this->originalName($u->file_name);
                $u->receipts = 0;
                $u->amount   = 0;
                $uploadsByDay[substr($u->created_at, 0, 10)][] = $u;
            }

            // receipt count / amount per upload (cancelled and deleted receipts excluded)
            $sums = [];
            foreach ($this->receiptRows($uploads->pluck('id')->all()) as $r) {
                if ($r->deleted_at || (int) $r->receipt_status === 2) {
                    continue;
                }
                if (!isset($sums[$r->upload_id])) {
                    $sums[$r->upload_id] = ['receipts' => 0, 'amount' => 0];
                }
                $sums[$r->upload_id]['receipts']++;
                $sums[$r->upload_id]['amount'] += $r->amount;
            }
            foreach ($uploadsByDay as $day => $list) {
                foreach ($list as $u) {
                    if (isset($sums[$u->id])) {
                        $u->receipts = $sums[$u->id]['receipts'];
                        $u->amount   = $sums[$u->id]['amount'];
                        $totalsByDay[$day]['receipts'] += $u->receipts;
                        $totalsByDay[$day]['amount']   += $u->amount;
                    }
                }
            }
        }

        return view('backoffice::Routine.pdc_bank_sheet_uploads', compact('days', 'uploadsByDay', 'totalsByDay', 'from', 'to'));
    }

    /** One posting run: the receipts it created and the sheet rows it did not post. */
    public function result($uploadId)
    {
        $upload = $this->uploadQuery()->where('u.id', (int) $uploadId)->first();
        if ($upload === null) {
            abort(404);
        }
        $upload->original_name = $this->originalName($upload->file_name);

        $receipts  = $this->receiptRows([$upload->id]);
        $notPosted = $this->notPostedRows([$upload->id]);

        return view('backoffice::Routine.pdc_bank_sheet_result', compact('upload', 'receipts', 'notPosted'));
    }

    /** The result page as an Excel workbook: receipts created + cheques not posted. */
    public function resultExcel($uploadId)
    {
        $upload = $this->uploadQuery()->where('u.id', (int) $uploadId)->first();
        if ($upload === null) {
            abort(404);
        }
        $upload->original_name = $this->originalName($upload->file_name);

        $receipts  = $this->receiptRows([$upload->id]);
        $notPosted = $this->notPostedRows([$upload->id]);

        $fileName = 'bank_sheet_result_' . $upload->id . '_' . substr($upload->created_at, 0, 10) . '.xlsx';
        return \Excel::download(new PdcBankSheetResultExport($upload, $receipts, $notPosted), $fileName);
    }

    /** Every receipt from the uploads posted on one date, across all of that day's uploads. */
    public function day($date)
    {
        $date = $this->validDate($date);
        if ($date === null) {
            abort(404);
        }

        $uploads = $this->uploadQuery()->whereRaw('u.created_at::date = ?', [$date])->orderBy('u.id')->get();
        foreach ($uploads as $u) {
            $u->original_name = $this->originalName($u->file_name);
        }
        $ids = $uploads->pluck('id')->all();

        $receipts  = $this->receiptRows($ids);
        $notPosted = $this->notPostedRows($ids);

        return view('backoffice::Routine.pdc_bank_sheet_day', compact('date', 'uploads', 'receipts', 'notPosted'));
    }

    /**
     * Cheques still waiting: every accepted sheet cheque from any upload whose latest
     * upload did not post it and whose PDC has not been posted since (by a later upload
     * or the PDC Posting screen). One line per cheque however often it was uploaded.
     */
    public function pending(Request $request)
    {
        $filters = $this->pendingFilters($request);
        list($rows, $statusCounts) = $this->pendingRows($filters);

        $total = 0.0;
        foreach ($rows as $r) {
            $total += (float) $r->amount;
        }
        $page = max(1, (int) $request->input('page', 1));
        $pending = new LengthAwarePaginator(
            array_slice($rows, ($page - 1) * self::PER_PAGE, self::PER_PAGE),
            count($rows), self::PER_PAGE, $page,
            ['path' => route('pdcBankSheetPending')]
        );
        $pending->appends($request->except('page'));

        return view('backoffice::Routine.pdc_bank_sheet_pending', [
            'pending'      => $pending,
            'filters'      => $filters,
            'statusCounts' => $statusCounts,
            'total'        => $total,
            'statuses'     => self::PENDING_STATUSES,
        ]);
    }

    /** The Pending cheques list (current filters) as an Excel sheet. */
    public function pendingExcel(Request $request)
    {
        list($rows) = $this->pendingRows($this->pendingFilters($request));
        return \Excel::download(new PdcBankSheetResultSheet('Pending cheques', BankSheetResultRows::pendingSheet($rows)),
            'bank_sheet_pending_cheques_' . date('Y-m-d') . '.xlsx');
    }

    // ── helpers ────────────────────────────────────────────────────────

    /** Row statuses that leave a cheque pending (the audit never stores ambiguous / vat_check: those are 'skipped'). */
    const PENDING_STATUSES = ['not_found', 'amount_mismatch', 'date_mismatch', 'unknown_bank', 'skipped', 'failed'];

    private function pendingFilters(Request $request)
    {
        $status = (string) $request->input('status', '');
        return [
            'from'   => $this->validDate($request->input('from')),
            'to'     => $this->validDate($request->input('to')),
            'status' => in_array($status, self::PENDING_STATUSES, true) ? $status : '',
            'cheque' => trim((string) $request->input('cheque', '')),
            'bank'   => trim((string) $request->input('bank', '')),
        ];
    }

    /**
     * [pending rows (newest cheque date first), count per status before the status filter].
     * The latest upload row per cheque (cheque no without leading zeros + sheet bank +
     * amount + cheque date) decides: posted / already posted / rejected there => done.
     */
    private function pendingRows(array $f)
    {
        $key = "ltrim(trim(ur.cheque_no), '0'), ur.bank_name, ur.amount, ur.cheque_date";
        $in  = implode(',', array_fill(0, count(self::PENDING_STATUSES), '?'));
        $where = [];
        $bind  = self::PENDING_STATUSES;
        if ($f['from']) {
            $where[] = 'x.cheque_date >= ?';
            $bind[] = $f['from'];
        }
        if ($f['to']) {
            $where[] = 'x.cheque_date <= ?';
            $bind[] = $f['to'];
        }
        if ($f['cheque'] !== '') {
            $where[] = "ltrim(trim(x.cheque_no), '0') LIKE ?";
            $bind[] = '%' . BankSheetPdcMatcher::normaliseChequeNo($f['cheque']) . '%';
        }
        if ($f['bank'] !== '') {
            $where[] = 'x.bank_name ILIKE ?';
            $bind[] = '%' . $f['bank'] . '%';
        }

        $rows = DB::select("
            SELECT x.*
            FROM (
                SELECT DISTINCT ON ($key)
                       ur.id, ur.upload_id, ur.sheet_row_no, ur.cheque_no, ur.bank_name, ur.amount, ur.cheque_date,
                       ur.status, ur.reason, u.created_at AS uploaded_at,
                       COUNT(*) OVER (PARTITION BY $key) AS times
                FROM pdc_bank_sheet_upload_rows ur
                JOIN pdc_bank_sheet_uploads u ON u.id = ur.upload_id
                ORDER BY $key, ur.upload_id DESC, ur.id DESC
            ) x
            WHERE x.status IN ($in)" . (count($where) ? ' AND ' . implode(' AND ', $where) : '') . "
            ORDER BY x.cheque_date DESC NULLS LAST, x.cheque_no, x.id", $bind);

        $rows = $this->withoutPostedSince($rows);

        $counts = array_fill_keys(self::PENDING_STATUSES, 0);
        $out = [];
        foreach ($rows as $r) {
            $counts[$r->status]++;
            if ($f['status'] === '' || $r->status === $f['status']) {
                $out[] = $r;
            }
        }
        return [$out, $counts];
    }

    /**
     * Drops pending rows whose cheque has been posted since. Not-found / unknown-bank rows go
     * on any posted PDC with that cheque no + bank in the date window; the rest only when the
     * posted PDCs add up to the sheet amount (an unposted PDC shared the number there).
     */
    private function withoutPostedSince(array $rows)
    {
        if (count($rows) === 0) {
            return $rows;
        }
        $sheetRows = [];
        foreach ($rows as $r) {
            $sheetRows[] = [
                'row_no'      => (int) $r->id,
                'cheque_no'   => (string) $r->cheque_no,
                'bank_code'   => BankSheetReader::bankCode((string) $r->bank_name),
                'amount'      => (float) $r->amount,
                'cheque_date' => $r->cheque_date ? substr((string) $r->cheque_date, 0, 10) : '',
            ];
        }
        $posted = [];
        foreach (array_chunk($sheetRows, 1000) as $chunk) {
            foreach ($this->postedPdcs($chunk) as $p) {
                $posted[$p->id] = $p;
            }
        }
        $found = BankSheetPostedCheques::find($sheetRows, array_values($posted), $this->bankMap());

        $out = [];
        foreach ($rows as $r) {
            $f = isset($found[(int) $r->id]) ? $found[(int) $r->id] : null;
            $anyAmount = in_array($r->status, [BankSheetPdcMatcher::NOT_FOUND, BankSheetPdcMatcher::UNKNOWN_BANK], true);
            if ($f !== null && ($f['same_amount'] || $anyAmount)) {
                continue;
            }
            $out[] = $r;
        }
        return $out;
    }

    /**
     * Reads the stored sheet, pulls the unposted PDCs sharing its cheque
     * numbers and matches them. Returns raw results plus the sections.
     */
    private function analyse($stored)
    {
        if (!preg_match('/^[A-Za-z0-9._-]+$/', $stored)) {
            throw new \RuntimeException('Invalid sheet reference');
        }
        $path = storage_path('app/' . self::STORE_DIR . '/' . $stored);
        if (!is_file($path)) {
            throw new \RuntimeException('Uploaded sheet not found; please upload it again');
        }

        $rows = (new BankSheetReader())->read($path);
        if (count($rows) === 0) {
            throw new \RuntimeException('No cheque rows found in the uploaded sheet');
        }

        $bankMap = $this->bankMap();
        $results = (new BankSheetPdcMatcher($bankMap))->match($rows, $this->candidatePdcs($rows));
        $results = $this->markAlreadyPosted($results, $bankMap);

        // Rows the bank rejected (NSF, signature, ...) are kept out of the
        // review: only accepted cheques are shown. They still reach the audit.
        $sections = ['ambiguous' => [], 'vat' => [], 'matched' => [], 'cannot' => [], 'posted' => []];
        $rejected = 0;
        foreach ($results as $r) {
            if ($r['status'] === BankSheetPdcMatcher::REJECTED) {
                $rejected++;
            } elseif ($r['status'] === BankSheetPdcMatcher::ALREADY_POSTED) {
                $sections['posted'][] = $r;
            } elseif ($r['status'] === BankSheetPdcMatcher::MATCHED) {
                $sections['matched'][] = $r;
            } elseif ($r['status'] === BankSheetPdcMatcher::AMBIGUOUS) {
                $sections['ambiguous'][] = $r;
            } elseif ($r['status'] === BankSheetPdcMatcher::VAT_CHECK) {
                $sections['vat'][] = $r;
            } else {
                $sections['cannot'][] = $r;
            }
        }
        $counts = [
            'total'     => count($results),
            'accepted'  => count($results) - $rejected,
            'rejected'  => $rejected,
            'ambiguous' => count($sections['ambiguous']),
            'vat'       => count($sections['vat']),
            'matched'   => count($sections['matched']),
            'cannot'    => count($sections['cannot']),
            'posted'    => count($sections['posted']),
        ];

        return ['results' => $results, 'sections' => $sections, 'counts' => $counts, 'batch_date' => BankSheetReader::batchDate($rows)];
    }

    /**
     * Rows with no unposted PDC whose cheque was posted already (an earlier upload of
     * the same sheet, or the PDC Posting screen) become ALREADY_POSTED: shown on their
     * own tab, never posted again. A date / amount mismatch row only when the posted
     * PDCs add up to the sheet amount, as there an unposted PDC also carries the number.
     */
    private function markAlreadyPosted(array $results, array $bankMap)
    {
        $check = [];
        foreach ($results as $i => $r) {
            if (in_array($r['status'], [BankSheetPdcMatcher::NOT_FOUND, BankSheetPdcMatcher::DATE_MISMATCH, BankSheetPdcMatcher::AMOUNT_MISMATCH], true)) {
                $check[$i] = $r['row'];
            }
        }
        if (count($check) === 0) {
            return $results;
        }

        $found = BankSheetPostedCheques::find(array_values($check), $this->postedPdcs(array_values($check)), $bankMap);
        if (count($found) === 0) {
            return $results;
        }
        $ids = [];
        foreach ($found as $f) {
            foreach ($f['pdcs'] as $p) {
                $ids[] = $p->id;
            }
        }
        $uploads = $this->postingUploads($ids);

        foreach ($check as $i => $row) {
            if (!isset($found[$row['row_no']])) {
                continue;
            }
            $f = $found[$row['row_no']];
            if ($results[$i]['status'] !== BankSheetPdcMatcher::NOT_FOUND && !$f['same_amount']) {
                continue;
            }
            $receipts = [];
            $upload = null;
            foreach ($f['pdcs'] as $p) {
                if ($p->pdc_receipt_no && !in_array($p->pdc_receipt_no, $receipts, true)) {
                    $receipts[] = $p->pdc_receipt_no;
                }
                if (isset($uploads[$p->id])) {
                    $upload = $uploads[$p->id];
                }
            }
            $posted = $f['pdcs'][0]->pdc_posted_date;
            $reason = 'Already posted' . ($receipts ? ': receipt ' . implode(', ', $receipts) : '')
                    . ($posted ? ' on ' . \Carbon\Carbon::parse($posted)->format('d/m/Y') : '')
                    . ($upload ? ' (bank sheet upload #' . $upload . ')' : ' (PDC Posting)')
                    . ($f['same_amount'] ? '' : '; PLMS amount ' . number_format($f['amount'], 3) . ' differs from the sheet');
            $results[$i] = array_merge($results[$i], [
                'status'        => BankSheetPdcMatcher::ALREADY_POSTED,
                'pdcs'          => [],
                'candidates'    => [],
                'reason'        => $reason,
                'posted'        => $f['pdcs'],
                'posted_amount' => $f['amount'],
                'same_amount'   => $f['same_amount'],
                'receipts'      => $receipts,
                'upload_id'     => $upload,
            ]);
        }
        return $results;
    }

    /** Posted PDCs whose cheque number appears on the given sheet rows. */
    private function postedPdcs(array $rows)
    {
        $q = Pdc::with(['tenantContractInfo.tenant', 'tenantContractInfo.building', 'tenantContractInfo.Unit', 'bankInfo'])
            ->whereIn('pdc_check_no', $this->chequeNumbers($rows))
            ->where('pdc_is_posted', 1)
            ->orderBy('id');
        $out = [];
        foreach ($q->get() as $pdc) {
            $out[] = $this->pdcObject($pdc);
        }
        return $out;
    }

    /** pdc id => id of the bank sheet upload that posted it (latest when several). */
    private function postingUploads(array $pdcIds)
    {
        $pdcIds = array_values(array_unique(array_map('intval', $pdcIds)));
        if (count($pdcIds) === 0) {
            return [];
        }
        $in = implode(',', array_fill(0, count($pdcIds), '?'));
        $rows = DB::select("
            SELECT x.pdc_id, MAX(x.upload_id) AS upload_id
            FROM (
                SELECT ur.upload_id, unnest(string_to_array(ur.pdc_ids, ','))::int AS pdc_id
                FROM pdc_bank_sheet_upload_rows ur
                WHERE ur.status = 'posted' AND ur.pdc_ids IS NOT NULL
            ) x
            WHERE x.pdc_id IN ($in)
            GROUP BY x.pdc_id", $pdcIds);
        $out = [];
        foreach ($rows as $r) {
            $out[(int) $r->pdc_id] = (int) $r->upload_id;
        }
        return $out;
    }

    private function paginate(array $items, Request $request, $pageName, $stored, $tab = null)
    {
        $page = max(1, (int) $request->input($pageName, 1));
        $paginator = new LengthAwarePaginator(
            array_slice($items, ($page - 1) * self::PER_PAGE, self::PER_PAGE),
            count($items),
            self::PER_PAGE,
            $page,
            ['path' => route('pdcBankSheetPreview', $stored), 'pageName' => $pageName]
        );
        $paginator->appends($request->except([$pageName]));
        if ($tab !== null) {
            $paginator->appends('tab', $tab); // paging a tab keeps that tab open
        }
        return $paginator;
    }

    private function selectionKey($stored)
    {
        return 'pdc_bank_sheet.' . $stored;
    }

    /** @return array ['unticked' => [row_no => true], 'choice' => [row_no => "id,id"|""], 'vat' => [row_no => true] (ticked Rent + VAT row)] */
    private function selection(Request $request, $stored)
    {
        $s = (array) $request->session()->get($this->selectionKey($stored), []);
        return [
            'unticked' => isset($s['unticked']) ? (array) $s['unticked'] : [],
            'choice'   => isset($s['choice']) ? (array) $s['choice'] : [],
            'vat'      => isset($s['vat']) ? (array) $s['vat'] : [],
        ];
    }

    /**
     * Save the sheet's Pay Account (without the bank's leading zeros) onto the
     * given posted PDCs that have no account yet. Never overwrites one staff
     * entered. Runs inside the caller's per-cheque transaction.
     */
    private function fillAccountNo(array $pdcIds, $sheetAccount)
    {
        $account = BankSheetPdcMatcher::normaliseAccountNo($sheetAccount);
        if ($account === null || count($pdcIds) === 0) {
            return;
        }
        Pdc::whereIn('id', $pdcIds)
            ->where(function ($q) {
                $q->whereNull('pdc_account_no')->orWhere('pdc_account_no', '');
            })
            ->update(['pdc_account_no' => $account]);
    }

    /** Posting runs with deposit bank and poster names. */
    private function uploadQuery()
    {
        return DB::table('pdc_bank_sheet_uploads as u')
            ->leftJoin('bank as b', 'b.id', '=', 'u.bank_id')
            ->leftJoin('users as us', 'us.id', '=', 'u.created_by')
            ->select('u.*', 'b.bank_name', 'b.bank_code', 'us.username');
    }

    /**
     * Receipts created by the given posting runs, found through the audit trail:
     * upload row -> pdc_ids -> pdc.pdc_receipt_no -> receipts_generation
     * (receipt numbers are unique). Receipts deleted or cancelled since are
     * still returned, so the page can show that status.
     */
    private function receiptRows(array $uploadIds)
    {
        $uploadIds = array_values(array_filter(array_map('intval', $uploadIds)));
        if (count($uploadIds) === 0) {
            return [];
        }
        $in = implode(',', array_fill(0, count($uploadIds), '?'));

        $rows = DB::select("
            SELECT y.upload_id, y.sheet_row_no, y.sheet_cheque_no, y.sheet_bank_name,
                   (y.is_vat = 1 OR y.pdc_type = 3) AS is_vat,
                   y.row_reason,
                   rg.id AS receipt_id, rg.receipts_generation_receipt_no AS receipt_no,
                   rg.receipts_generation_receipt_date AS receipt_date,
                   rg.receipts_generation_amt AS amount,
                   rg.receipts_generation_type AS receipt_type,
                   rg.receipts_generation_status AS receipt_status,
                   rg.receipts_generation_approval_status AS approval_status,
                   rg.deleted_at,
                   tc.tenant_contract_no, t.tenant_name, b.building_name, un.unit_no
            FROM (
                SELECT DISTINCT ON (x.receipt_id) x.*
                FROM (
                    SELECT ur.upload_id, ur.sheet_row_no, ur.cheque_no AS sheet_cheque_no, ur.bank_name AS sheet_bank_name,
                           rg.id AS receipt_id, 0 AS is_vat, p.pdc_type, ur.reason AS row_reason
                    FROM pdc_bank_sheet_upload_rows ur
                    JOIN pdc p ON p.id = ANY (string_to_array(ur.pdc_ids, ',')::int[])
                    JOIN receipts_generation rg ON rg.receipts_generation_receipt_no = p.pdc_receipt_no
                                               AND rg.tenant_contract_id = p.tenant_contract_id
                    WHERE ur.upload_id IN ($in) AND ur.status = 'posted' AND ur.pdc_ids IS NOT NULL
                    UNION ALL
                    SELECT ur.upload_id, ur.sheet_row_no, ur.cheque_no, ur.bank_name, rg.id, 1, 3, ur.reason AS row_reason
                    FROM pdc_bank_sheet_upload_rows ur
                    JOIN receipts_generation rg ON rg.receipts_generation_receipt_no = ANY (string_to_array(ur.vat_receipt_no, ','))
                    WHERE ur.upload_id IN ($in) AND ur.status = 'posted' AND ur.vat_receipt_no IS NOT NULL
                ) x
                ORDER BY x.receipt_id, x.is_vat
            ) y
            JOIN receipts_generation rg ON rg.id = y.receipt_id
            LEFT JOIN tenant_contracts tc ON tc.id = rg.tenant_contract_id
            LEFT JOIN tenant t ON t.id = tc.tenant_id
            LEFT JOIN buildings b ON b.id = tc.building_id
            LEFT JOIN units un ON un.id = tc.unit_id
            ORDER BY y.upload_id, y.sheet_row_no, y.is_vat, rg.receipts_generation_receipt_no", array_merge($uploadIds, $uploadIds));

        return $rows;
    }

    /** Sheet rows the runs did not post (bank-rejected rows are not shown on the review page either). */
    private function notPostedRows(array $uploadIds)
    {
        if (count($uploadIds) === 0) {
            return collect();
        }
        return DB::table('pdc_bank_sheet_upload_rows')
            ->whereIn('upload_id', $uploadIds)
            ->whereNotIn('status', ['posted', BankSheetPdcMatcher::REJECTED, BankSheetPdcMatcher::ALREADY_POSTED])
            ->orderBy('upload_id')
            ->orderBy('sheet_row_no')
            ->get();
    }

    /** 'Y-m-d' string or null. */
    private function validDate($value)
    {
        if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }
        list($y, $m, $d) = explode('-', $value);
        return checkdate((int) $m, (int) $d, (int) $y) ? $value : null;
    }

    /**
     * Adds a clearing sheet code from the sheet to a bank, next to the codes it already has
     * ("18" -> "18,45"). Needs the Bank master edit right. A code another bank already carries
     * is refused here (it is no longer unknown); moving it is a Bank master change.
     */
    public function saveClearingCode(Request $request, $stored)
    {
        if (!\Auth::user()->can('edit_bank')) {
            abort(403);
        }
        $this->validate($request, [
            'code'    => ['required', 'regex:/^\d{1,10}$/'],
            'bank_id' => 'required|integer|exists:bank,id',
        ], ['bank_id.required' => 'Choose the bank for this code.']);
        $back = redirect()->route('pdcBankSheetPreview', $stored);
        $code = (string) $request->code;
        $bank = Bank::findOrFail((int) $request->bank_id);

        if (in_array($code, BankClearingCodes::split($bank->bank_clearing_codes), true)) {
            return $back->with('success', 'Code ' . $code . ' is already on ' . $bank->bank_name . '.');
        }
        $others = Bank::where('id', '<>', $bank->id)->whereNotNull('bank_clearing_codes')->get()
            ->filter(function ($b) use ($code) { return in_array($code, BankClearingCodes::split($b->bank_clearing_codes), true); });
        if ($others->count() > 0) {
            // saved meanwhile (another tab / the Bank master): the reloaded preview already uses it
            return $back->with('error', 'Code ' . $code . ' is already on ' . $others->pluck('bank_name')->implode(', ')
                . '. If it belongs to ' . $bank->bank_name . ' instead, change it in the Bank master.');
        }

        $old = $bank->bank_clearing_codes;
        $bank->bank_clearing_codes = BankClearingCodes::add($old, $code);
        if (strlen($bank->bank_clearing_codes) > 100) {
            return $back->with('error', 'Too many codes on ' . $bank->bank_name . ' (100 characters at most).');
        }
        $bank->updated_by = \Auth::user()->id;
        $bank->save();
        \Log::info('Bank clearing code added from bank sheet', ['bank_id' => $bank->id, 'code' => $code, 'before' => $old,
            'after' => $bank->bank_clearing_codes, 'user_id' => \Auth::user()->id, 'sheet' => $stored]);

        return $back->with('success', 'Code ' . $code . ' added to ' . $bank->bank_name . ' (codes now ' . $bank->bank_clearing_codes
            . '). The sheet has been matched again.');
    }

    /** "20260920_163000_7_Cheque.xls" => "Cheque.xls" */
    private function originalName($stored)
    {
        return preg_replace('/^\d{8}_\d{6}_\d+_/', '', $stored);
    }

    /** bank_clearing_codes ("27,34") on the bank master => sheet code => [bank ids]. */
    private function bankMap()
    {
        $map = [];
        foreach (Bank::whereNotNull('bank_clearing_codes')->get() as $bank) {
            foreach (explode(',', $bank->bank_clearing_codes) as $code) {
                $code = trim($code);
                if ($code !== '') {
                    $map[$code][] = (int) $bank->id;
                }
            }
        }
        return $map;
    }

    /**
     * Unposted, uncancelled rent/deposit PDCs whose cheque number appears on
     * the sheet, as light objects the matcher and the preview both read.
     */
    /**
     * $forRejected: cheques the bank rejected may already be posted (receipt made)
     * or already marked bounced, so those are included; still never cleared or
     * cancelled for another reason.
     */
    private function candidatePdcs(array $rows, $forRejected = false)
    {
        $q = Pdc::with(['tenantContractInfo.tenant', 'tenantContractInfo.building', 'tenantContractInfo.Unit', 'bankInfo'])
            ->whereIn('pdc_check_no', $this->chequeNumbers($rows))
            ->whereNull('pdc_clear_date')
            ->whereIn('pdc_type', [1, 2, 3])
            ->orderBy('id');
        if ($forRejected) {
            $q->whereIn('pdc_cancel_reason', [0, BankSheetRejectedCheques::BOUNCED]);
        } else {
            $q->where('pdc_is_posted', 0)->where('pdc_cancel_reason', 0);
        }
        $out = [];
        foreach ($q->get() as $pdc) {
            $out[] = $this->pdcObject($pdc);
        }
        return $out;
    }

    /**
     * The cheque numbers of sheet rows as printed (e.g. 00205886) and without their
     * leading zeros (205886): PDCs are stored either way. The matchers compare both sides normalised.
     */
    private function chequeNumbers(array $rows)
    {
        $numbers = [];
        foreach ($rows as $r) {
            $printed = trim((string) $r['cheque_no']);
            if ($printed !== '') {
                $numbers[$printed] = true;
            }
            $numbers[BankSheetPdcMatcher::normaliseChequeNo($r['cheque_no'])] = true;
        }
        return array_keys($numbers);
    }

    /** A PDC as the light object the matchers and the pages read. */
    private function pdcObject(Pdc $pdc)
    {
        $contract = $pdc->tenantContractInfo;
        return (object) [
            'id'                 => (int) $pdc->id,
            'pdc_check_no'       => (string) $pdc->pdc_check_no,
            'bank_id'            => (int) $pdc->bank_id,
            'bank_name'          => $pdc->bankInfo ? $pdc->bankInfo->bank_name : '',
            'pdc_amt'            => (float) $pdc->pdc_amt,
            'pdc_check_date'     => $pdc->pdc_check_date ? $pdc->pdc_check_date->format('Y-m-d') : '',
            'pdc_type_name'      => $pdc->pdc_type_name,
            'tenant_contract_id' => (int) $pdc->tenant_contract_id,
            'tenant_id'          => $contract ? (int) $contract->tenant_id : 0,
            'tenant_name'        => $contract && $contract->tenant ? $contract->tenant->tenant_name : '',
            'tenant_contract_no' => $contract ? $contract->tenant_contract_no : '',
            'building_name'      => $contract && $contract->building ? $contract->building->building_name : '',
            'unit_no'            => $contract && $contract->Unit ? $contract->Unit->unit_code : '',
            'pdc_account_no'     => $pdc->pdc_account_no,
            'pdc_remark'         => (string) $pdc->pdc_remark,
            'pdc_type'           => (int) $pdc->pdc_type,
            'management_id'      => $contract && $contract->building ? (int) $contract->building->management_id : 0,
            'pdc_cancel_reason'  => (int) $pdc->pdc_cancel_reason,
            'pdc_is_posted'      => (int) $pdc->pdc_is_posted,
            'pdc_receipt_no'     => $pdc->pdc_receipt_no,
            'bounce_reason_name' => (int) $pdc->pdc_cancel_reason === BankSheetRejectedCheques::BOUNCED ? $pdc->pdc_cancel_reason_name : null,
            'pdc_posted_date'    => $pdc->pdc_posted_date ? substr((string) $pdc->pdc_posted_date, 0, 10) : null,
        ];
    }

    /** The candidate group whose pdc ids are exactly the submitted "id,id" list, or null. */
    private function candidateByIds(array $candidates, $idList)
    {
        $wanted = array_map('intval', explode(',', $idList));
        sort($wanted);
        foreach ($candidates as $group) {
            $ids = array_map('intval', array_column($group, 'id'));
            sort($ids);
            if ($ids === $wanted) {
                return $group;
            }
        }
        return null;
    }

    private function audit(Request $request, $stored, array $results, array $outcome, $posted, $pdcsDone)
    {
        $uploadId = DB::table('pdc_bank_sheet_uploads')->insertGetId([
            'file_name'        => $stored,
            'pdc_deposit_date' => $request->pdc_deposit_date,
            'bank_id'          => $request->bank_id,
            'rows_total'       => count($results),
            'rows_posted'      => $posted,
            'pdcs_posted'      => $pdcsDone,
            'created_by'       => \Auth::user()->id,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        $lines = [];
        foreach ($results as $r) {
            $rowNo  = $r['row']['row_no'];
            $status = $r['status'];
            $reason = $r['reason'];
            $ids    = [];
            $vatNo  = null;
            if (isset($outcome[$rowNo])) {
                list($status, $reason, $ids, $vatNo) = $outcome[$rowNo];
            } elseif (in_array($status, [BankSheetPdcMatcher::MATCHED, BankSheetPdcMatcher::AMBIGUOUS, BankSheetPdcMatcher::VAT_CHECK], true)) {
                $status = 'skipped';
            }
            $lines[] = [
                'upload_id'    => $uploadId,
                'sheet_row_no' => $rowNo,
                'cheque_no'    => $r['row']['cheque_no'],
                'bank_name'    => $r['row']['bank_name'],
                'amount'       => $r['row']['amount'],
                'cheque_date'  => $r['row']['cheque_date'],
                'status'       => $status,
                'reason'       => $reason !== null ? mb_substr($reason, 0, 500) : null,
                'pdc_ids'      => count($ids) ? implode(',', $ids) : null,
                'vat_receipt_no' => $vatNo !== '' ? $vatNo : null,
                'created_at'   => now(),
                'updated_at'   => now(),
            ];
        }
        foreach (array_chunk($lines, 200) as $chunk) {
            DB::table('pdc_bank_sheet_upload_rows')->insert($chunk);
        }

        return $uploadId;
    }
}
