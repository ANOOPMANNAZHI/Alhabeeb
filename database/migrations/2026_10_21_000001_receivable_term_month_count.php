<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Rent receivable: one month short for active contracts with a termination in their end month (2026-10-21).
 *
 * In the first branch of public.tenantrentreceivable() / tenantrentreceivablecompo() (contracts still active,
 * tenant_contract_status 1), a termination row dated in the contract's end month counts the months as
 * (year diff) * 12 + (month diff), without the + 1 the as-on-date count has. A contract starting on the 1st is
 * then charged one month less: TAG2401373 (01/09/2024 - 31/08/2025, rent 150, termination 31/08/2025 still at
 * stage 503) was charged 11 x 150 = 1,650, equal to the rent received, and dropped out of Legal Receivable v2
 * although 150 is owed.
 *
 * Fix: whole months like RentOutstanding::monthsBetween - + 1, less one when the termination day is before the
 * start day (16/09/2024 - 15/09/2025 stays 12) - and never more than the months up to the report date, so a
 * termination still ahead (TAG2503488, 30/11/2026) does not charge the coming month early. The live definitions
 * are patched in place; down() restores them.
 */
class ReceivableTermMonthCount extends Migration
{
    private $functions = [
        'tenantrentreceivable(date)',
        'tenantrentreceivablecompo(date, character varying, character varying, character varying, text, text)',
    ];

    const OLD = "(DATE_PART('year', tr.termination_date::date) - DATE_PART('year', tenant_contract_effective_date::date)) * 12 +
            (DATE_PART('month', tr.termination_date::date) - DATE_PART('month',tenant_contract_effective_date::date))
       else";

    const NEW = "LEAST((DATE_PART('year', tr.termination_date::date) - DATE_PART('year', tenant_contract_effective_date::date)) * 12 +
            (DATE_PART('month', tr.termination_date::date) - DATE_PART('month',tenant_contract_effective_date::date))
            + case when DATE_PART('day', tr.termination_date::date) >= DATE_PART('day', tenant_contract_effective_date::date) then 1 else 0 end,
            (DATE_PART('year', Date2::date) - DATE_PART('year', tenant_contract_effective_date::date)) * 12 +
            (DATE_PART('month', Date2::date) - DATE_PART('month',tenant_contract_effective_date::date))+1)
       else";

    public function up()
    {
        $this->apply(self::OLD, self::NEW);
    }

    public function down()
    {
        $this->apply(self::NEW, self::OLD);
    }

    /** Patch every function first, then run them, so a miss leaves the database unchanged. */
    private function apply($old, $new)
    {
        $patched = [];
        foreach ($this->functions as $fn) {
            $patched[] = $this->patch($fn, $old, $new);
        }
        foreach ($patched as $sql) {
            DB::unprepared($sql);
        }
    }

    private function patch($fn, $old, $new)
    {
        $sql = DB::selectOne("SELECT pg_get_functiondef('public.$fn'::regprocedure) AS d")->d;
        // the live definitions differ from local ones in spacing, line breaks and case (Date2/date2)
        $chars = preg_split('//u', preg_replace('/\s+/', '', $old), -1, PREG_SPLIT_NO_EMPTY);
        $pattern = '/' . implode('\s*', array_map(function ($c) { return preg_quote($c, '/'); }, $chars)) . '/iu';
        if (preg_match_all($pattern, $sql) !== 1) {
            $file = storage_path('logs/' . preg_replace('/\W.*/', '', $fn) . '_definition.sql');
            file_put_contents($file, $sql);
            throw new \RuntimeException("public.$fn: expected the termination month count exactly once; definition saved to $file");
        }
        return preg_replace_callback($pattern, function () use ($new) { return $new; }, $sql);
    }
}
