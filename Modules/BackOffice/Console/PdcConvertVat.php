<?php

namespace Modules\BackOffice\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\BackOffice\Entities\Pdc;
use Modules\BackOffice\Exports\PdcBankSheetResultSheet;
use Modules\BackOffice\Services\VatPdcClassifier;

/**
 * One-off with the VAT PDC type (user, 05/10/2026): unposted "Others" PDCs that
 * are VAT (VatPdcClassifier: near 5% of the agreement's rent, on the rent
 * cheque or on their own cheque) become pdc_type 4 "VAT", so the bank sheet and
 * the PDC Posting screen book them as VAT receipts. Posted, cleared and
 * cancelled PDCs are left as they are; "Others" that are charges stay "Others".
 * Dry run by default: writes the list to storage/app/pdc_vat_conversion/ for
 * checking; --apply changes the type (and saves the same list as the record).
 * Re-running changes nothing new. PHP < 7.4 in production: no arrow functions.
 */
class PdcConvertVat extends Command
{
    protected $signature = 'pdc:convert-vat {--apply : Change the type (default is a dry run)} {--from= : Only PDCs with a cheque date on or after this date (Y-m-d)}';
    protected $description = 'Move unposted "Others" PDCs that are VAT (near 5% of rent) to the VAT PDC type';

    public function handle()
    {
        $apply = (bool) $this->option('apply');
        $from  = $this->option('from');
        if ($from !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
            $this->error('--from must be a date like 2026-01-01');
            return;
        }

        $others = DB::table('pdc')
            ->where('pdc_type', Pdc::TYPE_OTHERS)
            ->where('pdc_is_posted', 0)
            ->whereNull('pdc_clear_date')
            ->where('pdc_cancel_reason', '0')
            ->when($from, function ($q) use ($from) { $q->where('pdc_check_date', '>=', $from); })
            ->select('id', 'tenant_contract_id', 'pdc_check_no', 'bank_id', 'pdc_amt', 'pdc_check_date', 'pdc_remark')
            ->orderBy('id')
            ->get()->all();
        $contractIds = array_values(array_unique(array_map(function ($o) { return (int) $o->tenant_contract_id; }, $others)));

        $rents = [];
        foreach (array_chunk($contractIds, 1000) as $chunk) {
            foreach (DB::table('pdc')->where('pdc_type', Pdc::TYPE_RENT)->whereIn('tenant_contract_id', $chunk)
                         ->select('id', 'tenant_contract_id', 'pdc_check_no', 'bank_id', 'pdc_amt', 'pdc_check_date')->get() as $r) {
                $rents[] = $r;
            }
        }

        $vat = VatPdcClassifier::classify($others, $rents);
        $this->info(count($others) . ' unposted "Others" PDCs checked; ' . count($vat) . ' are VAT');
        $kinds = [];
        foreach ($vat as $v) {
            $kinds[$v['kind']] = isset($kinds[$v['kind']]) ? $kinds[$v['kind']] + 1 : 1;
        }
        foreach ($kinds as $k => $n) {
            $this->line('  ' . $k . ': ' . $n);
        }
        $years = [];
        foreach ($others as $o) {
            if (isset($vat[(int) $o->id])) {
                $y = substr((string) $o->pdc_check_date, 0, 4);
                $years[$y] = isset($years[$y]) ? $years[$y] + 1 : 1;
            }
        }
        ksort($years);
        $this->line('  by cheque year: ' . implode(', ', array_map(function ($y, $n) { return $y . ': ' . $n; }, array_keys($years), $years)));

        $file = 'pdc_vat_conversion/pdc_vat_conversion_' . date('Ymd_His') . ($apply ? '_applied' : '_dry_run') . '.xlsx';
        \Excel::store(new PdcBankSheetResultSheet('VAT PDCs', $this->sheet($others, $vat)), $file);
        $this->line('List: storage/app/' . $file);

        if (!$apply) {
            $this->comment('Dry run: nothing changed. Run with --apply to change these to VAT.');
            return;
        }

        $changed = 0;
        foreach (array_chunk(array_keys($vat), 500) as $chunk) {
            $changed += Pdc::whereIn('id', $chunk)
                ->where('pdc_type', Pdc::TYPE_OTHERS)
                ->where('pdc_is_posted', 0)
                ->update(['pdc_type' => Pdc::TYPE_VAT]);
        }
        Log::info('pdc:convert-vat changed ' . $changed . ' PDCs from Others to VAT', ['list' => $file]);
        $this->info($changed . ' PDCs changed to VAT');
    }

    /** The checked list: one row per PDC that becomes VAT, with the rent it was compared to. */
    private function sheet(array $others, array $vat)
    {
        $byId = [];
        foreach ($others as $o) {
            $byId[(int) $o->id] = $o;
        }
        $info = [];
        foreach (array_chunk(array_keys($vat), 1000) as $chunk) {
            foreach (DB::table('pdc as p')
                         ->join('tenant_contracts as tc', 'tc.id', '=', 'p.tenant_contract_id')
                         ->leftJoin('tenant as t', 't.id', '=', 'tc.tenant_id')
                         ->leftJoin('buildings as b', 'b.id', '=', 'tc.building_id')
                         ->leftJoin('units as u', 'u.id', '=', 'tc.unit_id')
                         ->whereIn('p.id', $chunk)
                         ->select('p.id', 'tc.tenant_contract_no', 't.tenant_name', 'b.building_name', 'u.unit_no')->get() as $r) {
                $info[(int) $r->id] = $r;
            }
        }

        $rows = [['PDC Id', 'Agreement', 'Tenant', 'Building / Unit', 'Cheque No', 'Cheque Date', 'Amount', 'Remark',
                  'Rent Compared', '5% of Rent', 'Where']];
        foreach ($vat as $id => $v) {
            $o = $byId[$id];
            $i = isset($info[$id]) ? $info[$id] : null;
            $rows[] = [
                (int) $id,
                $i ? (string) $i->tenant_contract_no : '-',
                $i ? (string) $i->tenant_name : '-',
                $i ? $i->building_name . ' / ' . $i->unit_no : '-',
                (string) $o->pdc_check_no,
                $o->pdc_check_date ? date('d/m/Y', strtotime($o->pdc_check_date)) : '-',
                round((float) $o->pdc_amt, 3),
                (string) $o->pdc_remark,
                $v['rent'],
                $v['expected'],
                $v['kind'],
            ];
        }
        return ['rows' => $rows, 'heading_row' => 1, 'total_row' => null, 'amount_column' => 'G'];
    }
}
