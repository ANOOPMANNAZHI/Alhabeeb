<?php

namespace Modules\BackOffice\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\BackOffice\Entities\Pdc;
use Modules\BackOffice\Services\BankSheetPdcMatcher;
use Modules\BackOffice\Services\BankSheetReader;
use Modules\Masters\Entities\Bank;

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

        $matched = $this->paginate($sections['matched'], $request, 'mpage', $stored);
        $cannot  = $this->paginate($sections['cannot'], $request, 'cpage', $stored);

        $chosen = 0;
        foreach ($selection['choice'] as $v) {
            if ($v !== '') {
                $chosen++;
            }
        }
        $toPost = $analysis['counts']['matched'] - count($selection['unticked']) + $chosen;

        return view('backoffice::Routine.pdc_bank_sheet_preview', [
            'stored'    => $stored,
            'fileName'  => $this->originalName($stored),
            'banks'     => Bank::active()->where('accounts_bank', 1)->orderBy('bank_name', 'ASC')->get(),
            'ambiguous' => $sections['ambiguous'],
            'matched'   => $matched,
            'cannot'    => $cannot,
            'counts'    => $analysis['counts'],
            'selection' => $selection,
            'toPost'    => $toPost,
            'today'     => date('Y-m-d'),
        ]);
    }

    /** AJAX: remember an untick / re-tick of a matched row or a candidate choice for an ambiguous one. */
    public function select(Request $request, $stored)
    {
        $this->validate($request, [
            'row'   => 'required|integer',
            'kind'  => 'required|in:tick,choice',
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

        try {
            $analysis = $this->analyse($stored);
        } catch (\RuntimeException $e) {
            return redirect()->route('pdcPosting')->with('error', $e->getMessage());
        }

        $selection = $this->selection($request, $stored);

        $toPost = []; // sheet row_no => [pdc rows]
        foreach ($analysis['results'] as $r) {
            $rowNo = $r['row']['row_no'];
            if ($r['status'] === BankSheetPdcMatcher::MATCHED && !isset($selection['unticked'][$rowNo])) {
                $toPost[$rowNo] = $r['pdcs'];
            } elseif ($r['status'] === BankSheetPdcMatcher::AMBIGUOUS && !empty($selection['choice'][$rowNo])) {
                $group = $this->candidateByIds($r['candidates'], $selection['choice'][$rowNo]);
                if ($group !== null) {
                    $toPost[$rowNo] = $group;
                }
            }
        }

        if (count($toPost) === 0) {
            return redirect()->route('pdcBankSheetPreview', $stored)->with('error', 'Nothing selected to post');
        }

        $routines = app(RoutinesController::class);
        $posted   = 0;
        $pdcsDone = 0;
        $tillEnd  = [];
        $failed   = [];
        $outcome  = []; // row_no => [status, reason, pdc_ids]

        foreach ($toPost as $rowNo => $pdcs) {
            $ids = [];
            try {
                DB::transaction(function () use ($routines, $pdcs, $request, &$ids, &$tillEnd) {
                    foreach ($pdcs as $p) {
                        $pdc = Pdc::lockForUpdate()->find($p->id);
                        if ($pdc === null || (int) $pdc->pdc_is_posted === 1) {
                            throw new \RuntimeException('cheque ' . $p->pdc_check_no . ' was already posted');
                        }
                        $result = $routines->postSinglePdc($pdc, $request->pdc_deposit_date, $request->bank_id);
                        if ($result['posted']) {
                            $ids[] = $pdc->id;
                        } else {
                            $tillEnd[] = $result['contract_no'];
                        }
                    }
                });
                $posted++;
                $pdcsDone += count($ids);
                $outcome[$rowNo] = ['posted', null, $ids];
            } catch (\Exception $e) {
                $failed[] = $pdcs[0]->pdc_check_no . ': ' . $e->getMessage();
                $outcome[$rowNo] = ['failed', $e->getMessage(), []];
            }
        }

        $this->audit($request, $stored, $analysis['results'], $outcome, $posted, $pdcsDone);
        $request->session()->forget($this->selectionKey($stored));

        $msg = $posted . ' cheque(s) posted (' . $pdcsDone . ' PDC row(s)); '
             . $analysis['counts']['cannot'] . ' left for manual posting';
        if (count($tillEnd) > 0) {
            $msg .= '. Receipts already run to contract end for: ' . implode(', ', array_unique($tillEnd));
        }
        $redirect = redirect()->route('pdcPosting')->with('success', $msg);
        if (count($failed) > 0) {
            $redirect->with('error', 'Failed: ' . implode(' | ', $failed));
        }
        return $redirect;
    }

    // ── helpers ────────────────────────────────────────────────────────

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

        $results = (new BankSheetPdcMatcher($this->bankMap()))->match($rows, $this->candidatePdcs($rows));

        // Rows the bank rejected (NSF, signature, ...) are kept out of the
        // review: only accepted cheques are shown. They still reach the audit.
        $sections = ['ambiguous' => [], 'matched' => [], 'cannot' => []];
        $rejected = 0;
        foreach ($results as $r) {
            if ($r['status'] === BankSheetPdcMatcher::REJECTED) {
                $rejected++;
            } elseif ($r['status'] === BankSheetPdcMatcher::MATCHED) {
                $sections['matched'][] = $r;
            } elseif ($r['status'] === BankSheetPdcMatcher::AMBIGUOUS) {
                $sections['ambiguous'][] = $r;
            } else {
                $sections['cannot'][] = $r;
            }
        }
        $counts = [
            'total'     => count($results),
            'accepted'  => count($results) - $rejected,
            'rejected'  => $rejected,
            'ambiguous' => count($sections['ambiguous']),
            'matched'   => count($sections['matched']),
            'cannot'    => count($sections['cannot']),
        ];

        return ['results' => $results, 'sections' => $sections, 'counts' => $counts];
    }

    private function paginate(array $items, Request $request, $pageName, $stored)
    {
        $page = max(1, (int) $request->input($pageName, 1));
        $paginator = new LengthAwarePaginator(
            array_slice($items, ($page - 1) * self::PER_PAGE, self::PER_PAGE),
            count($items),
            self::PER_PAGE,
            $page,
            ['path' => route('pdcBankSheetPreview', $stored), 'pageName' => $pageName]
        );
        return $paginator->appends($request->except([$pageName]));
    }

    private function selectionKey($stored)
    {
        return 'pdc_bank_sheet.' . $stored;
    }

    /** @return array ['unticked' => [row_no => true], 'choice' => [row_no => "id,id"|""]] */
    private function selection(Request $request, $stored)
    {
        $s = (array) $request->session()->get($this->selectionKey($stored), []);
        return [
            'unticked' => isset($s['unticked']) ? (array) $s['unticked'] : [],
            'choice'   => isset($s['choice']) ? (array) $s['choice'] : [],
        ];
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
    private function candidatePdcs(array $rows)
    {
        $numbers = [];
        foreach ($rows as $r) {
            $numbers[BankSheetPdcMatcher::normaliseChequeNo($r['cheque_no'])] = true;
        }

        $pdcs = Pdc::with(['tenantContractInfo.tenant', 'tenantContractInfo.building', 'tenantContractInfo.Unit', 'bankInfo'])
            ->whereIn('pdc_check_no', array_keys($numbers))
            ->where('pdc_is_posted', 0)
            ->whereNull('pdc_clear_date')
            ->where('pdc_cancel_reason', 0)
            ->whereIn('pdc_type', [1, 2])
            ->orderBy('id')
            ->get();

        $out = [];
        foreach ($pdcs as $pdc) {
            $contract = $pdc->tenantContractInfo;
            $out[] = (object) [
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
            ];
        }
        return $out;
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
            if (isset($outcome[$rowNo])) {
                list($status, $reason, $ids) = $outcome[$rowNo];
            } elseif ($status === BankSheetPdcMatcher::MATCHED || $status === BankSheetPdcMatcher::AMBIGUOUS) {
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
                'created_at'   => now(),
                'updated_at'   => now(),
            ];
        }
        foreach (array_chunk($lines, 200) as $chunk) {
            DB::table('pdc_bank_sheet_upload_rows')->insert($chunk);
        }
    }
}
