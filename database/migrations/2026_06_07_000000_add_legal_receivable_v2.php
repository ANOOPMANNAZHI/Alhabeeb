<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddLegalReceivableV2 extends Migration
{
    public function up()
    {
        DB::unprepared('
CREATE OR REPLACE FUNCTION public.legalrentreceivable_v2(date2 date)
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
    WHERE r.contract_no IN (
        SELECT tc.tenant_contract_no
        FROM legal l
        INNER JOIN tenant_contracts tc ON tc.id = l.tenant_contract_id
    )
$func$;
        ');

        DB::unprepared('
CREATE OR REPLACE FUNCTION public.legalrentreceivablecompo_v2(
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
    WHERE r.contract_no IN (
        SELECT tc.tenant_contract_no
        FROM legal l
        INNER JOIN tenant_contracts tc ON tc.id = l.tenant_contract_id
    )
$func$;
        ');

        $parentMenuId = DB::table('menu')->where('id', 183)->value('id') ?? 0;

        $orderNext = DB::table('menu')
            ->where('parent_menu', $parentMenuId)
            ->count() + 1;

        $menuId = DB::table('menu')->insertGetId([
            'menu_name'   => 'Legal Receivable v2',
            'menu_icon'   => 'fa-university',
            'route_name'  => 'showLegalReceivablesReportV2',
            'url_key'     => 'showLegalReceivablesReportV2',
            'parent_menu' => $parentMenuId,
            'menutype'    => 2,
            'menu_order'  => $orderNext,
            'status'      => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        DB::table('permissions')->insert([
            'name'       => 'view_legal_receivables_v2',
            'guard_name' => 'web',
            'menu_id'    => $menuId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $permission = DB::table('permissions')
            ->where('name', 'view_legal_receivables_v2')
            ->first();

        if ($permission) {
            $roles = DB::table('roles')->pluck('id');
            foreach ($roles as $roleId) {
                DB::table('role_has_permissions')->insert([
                    'permission_id' => $permission->id,
                    'role_id'       => $roleId,
                ]);
            }
        }

        app('cache')->forget('spatie.permission.cache');
    }

    public function down()
    {
        DB::unprepared('DROP FUNCTION IF EXISTS public.legalrentreceivable_v2(date);');
        DB::unprepared('DROP FUNCTION IF EXISTS public.legalrentreceivablecompo_v2(date, varchar, varchar, varchar, text, text);');

        $menu = DB::table('menu')->where('route_name', 'showLegalReceivablesReportV2')->first();
        if ($menu) {
            $permission = DB::table('permissions')->where('menu_id', $menu->id)->first();
            if ($permission) {
                DB::table('role_has_permissions')->where('permission_id', $permission->id)->delete();
                DB::table('permissions')->where('id', $permission->id)->delete();
            }
            DB::table('menu')->where('id', $menu->id)->delete();
        }

        app('cache')->forget('spatie.permission.cache');
    }
}
