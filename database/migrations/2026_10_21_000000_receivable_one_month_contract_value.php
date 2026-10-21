<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Rent receivable: contracts whose contract value was saved as one month's rent (2026-10-21).
 *
 * public.tenantrentreceivable() / tenantrentreceivablecompo() (behind Tenant Receivable V2, Legal Receivable V2
 * and Tenant Aging) take the contract value as the amount due for an expired contract and for one terminated on
 * its end date. About 165 old contracts hold one month's rent there (TAG1700079: rent 1,575 x 12 months, value
 * 1,575, received 13,224.5), so they looked overpaid and dropped out; 22 legal contracts owing 69,349.4 vanished.
 *
 * Fix: when the value equals the monthly rent and the contract dates span more than one month, the functions use
 * rent x months between effective and valid-to date (the rule every other contract's value follows). Any other
 * value is kept, discounts and part months included. The live definitions are patched in place so earlier fixes
 * stay; down() puts the original text back.
 */
class ReceivableOneMonthContractValue extends Migration
{
    private $functions = [
        'tenantrentreceivable(date)',
        'tenantrentreceivablecompo(date, character varying, character varying, character varying, text, text)',
    ];

    public function up()
    {
        foreach ($this->functions as $fn) {
            DB::unprepared($this->patch($this->definition($fn), $this->replacements(), $fn));
        }
    }

    public function down()
    {
        foreach ($this->functions as $fn) {
            DB::unprepared($this->patch($this->definition($fn), array_flip($this->replacements()), $fn));
        }
    }

    private function definition($fn)
    {
        return DB::selectOne("SELECT pg_get_functiondef('public.$fn'::regprocedure) AS d")->d;
    }

    /** old text => new text; each old text must be present, else nothing is changed. */
    private function replacements()
    {
        $value = $this->value();
        return [
            'tc.tenant_contract_value as contractvalue' => $value . ' as contractvalue',
            'CAST (tc.tenant_contract_value AS float)' => 'CAST (' . $value . ' AS float)',
        ];
    }

    /** Contract value as text (same type as the column), one-month values replaced by rent x months. */
    private function value()
    {
        $months = "round((tc.tenant_contract_valid_to_date::date - tc.tenant_contract_effective_date::date + 1) / 30.4375)";
        // nested CASE so the numeric cast only runs on numeric text
        return "(CASE WHEN tc.tenant_contract_value ~ '^\\s*[0-9]+(\\.[0-9]+)?\\s*\$' THEN"
            . " CASE WHEN tc.tenant_contract_rent > 0 AND trim(tc.tenant_contract_value)::numeric = tc.tenant_contract_rent::numeric"
            . " AND $months > 1 THEN (tc.tenant_contract_rent::numeric * $months)::varchar"
            . " ELSE tc.tenant_contract_value END ELSE tc.tenant_contract_value END)";
    }

    private function patch($sql, array $replacements, $fn)
    {
        foreach ($replacements as $old => $new) {
            if (strpos($sql, $old) === false) {
                throw new \RuntimeException("public.$fn: expected text not found: $old");
            }
            $sql = str_replace($old, $new, $sql);
        }
        return $sql;
    }
}
