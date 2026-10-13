<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Legal module phase 4 (plan 2026-10-13): Legal Receivable V2 nets out legal settlement waivers.
 * legalrentreceivable_v2 / legalrentreceivablecompo_v2 keep their columns; netamtdue becomes
 * max(netamtdue - live waivers approved by date2, 0) and fully waived contracts drop out, like any
 * contract with nothing due. amount_received stays the money received. down() restores 2026_06_07_000000.
 */
class LegalReceivableV2NetOfWaivers extends Migration
{
    public function up()
    {
        DB::unprepared($this->plain(true));
        DB::unprepared($this->compo(true));
    }

    public function down()
    {
        DB::unprepared($this->plain(false));
        DB::unprepared($this->compo(false));
    }

    private function columns($compo)
    {
        return 'termination_date date, tenant_name text, contract_no character varying, unit_code character varying,
    start_date date, end_date date, paymentmode text, lastpaid_till date, receipt_date date, rentper_month numeric,
    contract_value numeric, amount_received numeric, pdc text, pdc_closed text, contact_person text, netamtdue numeric,
    totaldue numeric, contact_no character varying, buildingname text,' . ($compo ? ' building_no character varying,' : '') . '
    management_type text, employee_name text';
    }

    private function body($source, $compo, $net)
    {
        $netExpr = $net ? 'GREATEST(r.netamtdue - coalesce(wv.waived, 0), 0)' : 'r.netamtdue';
        $waiverJoin = $net ? "
    LEFT JOIN (
        SELECT tc.tenant_contract_no, sum(lw.amount) AS waived
        FROM legal_waivers lw
        INNER JOIN tenant_contracts tc ON tc.id = lw.tenant_contract_id
        WHERE lw.cancelled_at IS NULL AND lw.approved_at::date <= date2
        GROUP BY tc.tenant_contract_no
    ) wv ON wv.tenant_contract_no = r.contract_no" : '';
        $waiverWhere = $net ? "
      AND r.netamtdue - coalesce(wv.waived, 0) > 0" : '';
        return "
    SELECT r.termination_date, r.tenant_name, r.contract_no, r.unit_code, r.start_date, r.end_date, r.paymentmode,
           r.lastpaid_till, r.receipt_date, r.rentper_month, r.contract_value, r.amount_received, r.pdc, r.pdc_closed,
           r.contact_person, $netExpr, r.totaldue, r.contact_no, r.buildingname," . ($compo ? ' r.building_no,' : '') . "
           mt.management_types_name, emp_sub.employee_name
    FROM $source r
    LEFT JOIN buildings b ON b.building_name = r.buildingname AND b.building_status = '1'
    LEFT JOIN management_types mt ON mt.id = b.management_id
    LEFT JOIN (
        SELECT pb.building_id, emp2.employee_name
        FROM employees emp2
        LEFT JOIN users us ON emp2.id = us.user_type_id
        LEFT JOIN are_buildings areb ON us.id = areb.user_id
        LEFT JOIN preferred_buildings pb ON areb.id = pb.are_building_id
        WHERE pb.assign_to IS NULL
    ) emp_sub ON emp_sub.building_id = b.id$waiverJoin
    WHERE r.contract_no IN (
        SELECT tc.tenant_contract_no
        FROM legal l
        INNER JOIN tenant_contracts tc ON tc.id = l.tenant_contract_id
    )$waiverWhere";
    }

    private function plain($net)
    {
        return "CREATE OR REPLACE FUNCTION public.legalrentreceivable_v2(date2 date)
RETURNS TABLE(" . $this->columns(false) . ")
LANGUAGE SQL STABLE AS
\$func\$" . $this->body('tenantrentreceivable(date2)', false, $net) . "
\$func\$;";
    }

    private function compo($net)
    {
        return "CREATE OR REPLACE FUNCTION public.legalrentreceivablecompo_v2(
    date2 date, tenantnamepara character varying, buildingnamepara character varying, buildingno character varying,
    managementtypesname text, employeename text)
RETURNS TABLE(" . $this->columns(true) . ")
LANGUAGE SQL STABLE AS
\$func\$" . $this->body('tenantrentreceivablecompo(date2, tenantnamepara, buildingnamepara, buildingno, managementtypesname, employeename)', true, $net) . "
\$func\$;";
    }
}
