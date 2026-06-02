<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ExcludeLegalFromTenantReceivableV2 extends Migration
{
    public function up()
    {
        // Recreate tenantrentreceivable_v2 excluding all contracts under legal (regardless of status)
        DB::unprepared('
CREATE OR REPLACE FUNCTION public.tenantrentreceivable_v2(date2 date)
RETURNS TABLE(
    termination_date date,
    tenant_name text,
    contract_no character varying,
    unit_code character varying,
    start_date date,
    end_date date,
    paymentmode text,
    lastpaid_till date,
    receipt_date date,
    rentper_month numeric,
    contract_value numeric,
    amount_received numeric,
    pdc text,
    pdc_closed text,
    contact_person text,
    netamtdue numeric,
    totaldue numeric,
    contact_no character varying,
    buildingname text,
    management_type text,
    employee_name text
)
LANGUAGE SQL STABLE AS
$func$
    SELECT
        r.termination_date,
        r.tenant_name,
        r.contract_no,
        r.unit_code,
        r.start_date,
        r.end_date,
        r.paymentmode,
        r.lastpaid_till,
        r.receipt_date,
        r.rentper_month,
        r.contract_value,
        r.amount_received,
        r.pdc,
        r.pdc_closed,
        r.contact_person,
        r.netamtdue,
        r.totaldue,
        r.contact_no,
        r.buildingname,
        mt.management_types_name,
        emp_sub.employee_name
    FROM tenantrentreceivable(date2) r
    LEFT JOIN buildings b ON b.building_name = r.buildingname AND b.building_status = \'1\'
    LEFT JOIN management_types mt ON mt.id = b.management_id
    LEFT JOIN (
        SELECT pb.building_id, emp2.employee_name
        FROM employees emp2
        LEFT JOIN users us ON emp2.id = us.user_type_id
        LEFT JOIN are_buildings areb ON us.id = areb.user_id
        LEFT JOIN preferred_buildings pb ON areb.id = pb.are_building_id
        WHERE pb.assign_to IS NULL
    ) emp_sub ON emp_sub.building_id = b.id
    WHERE r.contract_no NOT IN (
        SELECT tc.tenant_contract_no
        FROM legal l
        INNER JOIN tenant_contracts tc ON tc.id = l.tenant_contract_id
    )
$func$;
        ');

        // Recreate tenantrentreceivablecompo_v2 excluding all contracts under legal (regardless of status)
        DB::unprepared('
CREATE OR REPLACE FUNCTION public.tenantrentreceivablecompo_v2(
    date2 date,
    tenantnamepara character varying,
    buildingnamepara character varying,
    buildingno character varying,
    managementtypesname text,
    employeename text
)
RETURNS TABLE(
    termination_date date,
    tenant_name text,
    contract_no character varying,
    unit_code character varying,
    start_date date,
    end_date date,
    paymentmode text,
    lastpaid_till date,
    receipt_date date,
    rentper_month numeric,
    contract_value numeric,
    amount_received numeric,
    pdc text,
    pdc_closed text,
    contact_person text,
    netamtdue numeric,
    totaldue numeric,
    contact_no character varying,
    buildingname text,
    building_no character varying,
    management_type text,
    employee_name text
)
LANGUAGE SQL STABLE AS
$func$
    SELECT
        r.termination_date,
        r.tenant_name,
        r.contract_no,
        r.unit_code,
        r.start_date,
        r.end_date,
        r.paymentmode,
        r.lastpaid_till,
        r.receipt_date,
        r.rentper_month,
        r.contract_value,
        r.amount_received,
        r.pdc,
        r.pdc_closed,
        r.contact_person,
        r.netamtdue,
        r.totaldue,
        r.contact_no,
        r.buildingname,
        r.building_no,
        mt.management_types_name,
        emp_sub.employee_name
    FROM tenantrentreceivablecompo(date2, tenantnamepara, buildingnamepara, buildingno, managementtypesname, employeename) r
    LEFT JOIN buildings b ON b.building_name = r.buildingname AND b.building_status = \'1\'
    LEFT JOIN management_types mt ON mt.id = b.management_id
    LEFT JOIN (
        SELECT pb.building_id, emp2.employee_name
        FROM employees emp2
        LEFT JOIN users us ON emp2.id = us.user_type_id
        LEFT JOIN are_buildings areb ON us.id = areb.user_id
        LEFT JOIN preferred_buildings pb ON areb.id = pb.are_building_id
        WHERE pb.assign_to IS NULL
    ) emp_sub ON emp_sub.building_id = b.id
    WHERE r.contract_no NOT IN (
        SELECT tc.tenant_contract_no
        FROM legal l
        INNER JOIN tenant_contracts tc ON tc.id = l.tenant_contract_id
    )
$func$;
        ');
    }

    public function down()
    {
        // Revert to version without legal exclusion (re-run previous migration's up)
        DB::unprepared('
CREATE OR REPLACE FUNCTION public.tenantrentreceivable_v2(date2 date)
RETURNS TABLE(
    termination_date date, tenant_name text, contract_no character varying,
    unit_code character varying, start_date date, end_date date, paymentmode text,
    lastpaid_till date, receipt_date date, rentper_month numeric, contract_value numeric,
    amount_received numeric, pdc text, pdc_closed text, contact_person text,
    netamtdue numeric, totaldue numeric, contact_no character varying, buildingname text,
    management_type text, employee_name text
)
LANGUAGE SQL STABLE AS
$func$
    SELECT r.termination_date, r.tenant_name, r.contract_no, r.unit_code,
           r.start_date, r.end_date, r.paymentmode, r.lastpaid_till, r.receipt_date,
           r.rentper_month, r.contract_value, r.amount_received, r.pdc, r.pdc_closed,
           r.contact_person, r.netamtdue, r.totaldue, r.contact_no, r.buildingname,
           mt.management_types_name, emp_sub.employee_name
    FROM tenantrentreceivable(date2) r
    LEFT JOIN buildings b ON b.building_name = r.buildingname AND b.building_status = \'1\'
    LEFT JOIN management_types mt ON mt.id = b.management_id
    LEFT JOIN (
        SELECT pb.building_id, emp2.employee_name FROM employees emp2
        LEFT JOIN users us ON emp2.id = us.user_type_id
        LEFT JOIN are_buildings areb ON us.id = areb.user_id
        LEFT JOIN preferred_buildings pb ON areb.id = pb.are_building_id
        WHERE pb.assign_to IS NULL
    ) emp_sub ON emp_sub.building_id = b.id
$func$;
        ');
    }
}
