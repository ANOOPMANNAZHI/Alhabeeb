<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

/**
 * Fixes public.vacant_unit_date() and public.vacant_unit_compo() (used by the
 * "Report on Vacant Unit" screen / Jasper reports vacant_unit*.jrxml).
 *
 * Bug: for a unit with more than one terminated contract, the inner "last
 * terminated contract" subquery picked tenant_contract_no / contract_rent /
 * contract_end / term_end via independent MAX() aggregates grouped by unit,
 * instead of pulling them all from the single most-recently-terminated
 * contract row. Dates ended up correct (MAX(termination_date) really is the
 * latest) but the rent could come from an older, unrelated contract, since
 * MAX(tenant_contract_rent) is maxed independently of which contract that
 * belonged to (e.g. Al Khuwair House unit 33 showed rent 300 from a contract
 * terminated in 2020 instead of 225 from the actual last contract terminated
 * 2026-06-30).
 *
 * Fix: replace the GROUP BY + MAX() aggregates with
 * DISTINCT ON (u.id) ... ORDER BY termination_date DESC, tr.id DESC so every
 * column comes from the same (latest) terminated contract row.
 */
class FixVacantUnitReportRentFunctions extends Migration
{
    public function up()
    {
        DB::unprepared($this->fixedFunctionsSql());
    }

    public function down()
    {
        DB::unprepared($this->originalFunctionsSql());
    }

    private function fixedFunctionsSql(): string
    {
        return <<<'SQL'
CREATE OR REPLACE FUNCTION public.vacant_unit_date(date2 date)
 RETURNS TABLE(bldgname character varying, untno character varying, untstats integer, contract_end_date timestamp without time zone, termination_end_date timestamp without time zone, vacant_from_date timestamp without time zone, vacant_to_date timestamp without time zone, rent_pm numeric, unittype character varying, location_name character varying, days_btwn numeric, bldg_no character varying)
 LANGUAGE plpgsql
AS $function$BEGIN
  RETURN QUERY
    WITH
	a as
	(select  bom.buildingname,bom.buildingno,bom.unitno,bom.unitstatus,bom.terminationend_date,
	    bom.vacant_from,Date2 as vacant_to,bom.rentpm,bom.unit_type,bom.locationname,bom.contractend_date,
	    (SELECT DATE_PART('day', Date2::timestamp - bom.vacant_from))
		as days,
		bom.managementtype
		FROM
    (
        SELECT DISTINCT ON (u.id)
            r.contract,
            b.building_name AS buildingname,
            b.building_no AS buildingno,
            u.unit_vaccant_status AS unitstatus,
            r.id AS unitid,
            CASE
                WHEN r.unit_no IS NOT NULL THEN r.unit_no
                ELSE u.unit_no
            END AS unitno,
            r.contract_end AS contractend_date,
            r.term_end AS terminationend_date,
            CASE
                WHEN (CASE WHEN r.term_end IS NULL THEN r.contract_end ELSE r.term_end END) IS NULL
                    THEN u.created_at
                ELSE (CASE WHEN r.term_end IS NULL THEN r.contract_end ELSE r.term_end + 1 END)
            END AS vacant_from,
            CASE
                WHEN r.contract_rent IS NULL THEN u.unit_base_rent::float
                ELSE r.contract_rent
            END AS rentpm,
            ut.unit_types_name AS unit_type,
            l.locations_name AS locationname,
            mt.management_types_name AS managementtype,
            u.unit_vaccant_status AS unitvacantstatus
        FROM
            units u
        LEFT JOIN
            tenant_contracts tc ON tc.unit_id = u.id
        LEFT JOIN
            unit_types ut ON ut.id = u.unit_type_id
        LEFT JOIN
            termination tr ON tr.contract_id = tc.id
        LEFT JOIN
            buildings b ON b.id = u.building_id
        LEFT JOIN
            management_types mt ON mt.id = b.management_id
        LEFT JOIN
            locations l ON l.id = b.location_id
        LEFT JOIN
            (
                SELECT DISTINCT ON (u.id)
                    tr.id AS trid,
                    u.id AS id,
                    u.unit_no AS unit_no,
                    tc.tenant_contract_no AS contract,
                    tc.tenant_contract_rent AS contract_rent,
                    tc.tenant_contract_valid_to_date AS contract_end,
                    tr.termination_date AS term_end
                FROM
                    termination tr
                LEFT JOIN
                    tenant_contracts tc ON tc.id = tr.contract_id
                INNER JOIN
                    units u ON tc.unit_id = u.id
                LEFT JOIN
                    buildings b ON b.id = tc.building_id
                WHERE
                    tr.work_flow_processes_code = '505'
                ORDER BY
                    u.id, tr.termination_date DESC, tr.id DESC
            ) r ON r.id = tc.unit_id
        WHERE
            u.unit_vaccant_status = '0'
            AND unit_status = '1'
            AND b.building_status = '1'
        ORDER BY
            u.id, r.term_end,b.building_name DESC
    ) AS bom
	 where (bom.vacant_from <= Date2)


	),

    b as
	(
      SELECT * FROM a
	)

    SELECT b.buildingname::VARCHAR,
           b.unitno::VARCHAR,
           b.unitstatus::INT,
           b.contractend_date::TIMESTAMP WITHOUT TIME ZONE,
		   b.terminationend_date::TIMESTAMP WITHOUT TIME ZONE,
		   b.vacant_from::TIMESTAMP WITHOUT TIME ZONE,
		   b.vacant_to::TIMESTAMP WITHOUT TIME ZONE,
		   b.rentpm::NUMERIC,
		   b.unit_type::VARCHAR,
		   b.locationname::VARCHAR,
		   b.days::NUMERIC,
		   b.buildingno::VARCHAR
    FROM b order by b.buildingname::VARCHAR, b.unitno::VARCHAR;
END;
$function$;

CREATE OR REPLACE FUNCTION public.vacant_unit_compo(date2 date, building_name character varying, mngnt_type character varying)
 RETURNS TABLE(bldgname character varying, untno character varying, untstats integer, contract_end_date timestamp without time zone, termination_end_date timestamp without time zone, vacant_from_date timestamp without time zone, vacant_to_date timestamp without time zone, rent_pm numeric, unittype character varying, location_name character varying, days_btwn numeric, bldg_no character varying)
 LANGUAGE plpgsql
AS $function$BEGIN
  RETURN QUERY
    WITH
	a as
	(select  bom.buildingname,bom.buildingno,bom.unitno,bom.unitstatus,bom.terminationend_date,
	    bom.vacant_from,Date2 as vacant_to,bom.rentpm,bom.unit_type,bom.locationname,bom.contractend_date,
	    (SELECT DATE_PART('day', Date2::timestamp - bom.vacant_from))
		as days,
		bom.managementtype
		FROM
    (
        SELECT DISTINCT ON (u.id)
            r.contract,
            b.building_name AS buildingname,
            b.building_no AS buildingno,
            u.unit_vaccant_status AS unitstatus,
            r.id AS unitid,
            CASE
                WHEN r.unit_no IS NOT NULL THEN r.unit_no
                ELSE u.unit_no
            END AS unitno,
            r.contract_end AS contractend_date,
            r.term_end AS terminationend_date,
            CASE
                WHEN (CASE WHEN r.term_end IS NULL THEN r.contract_end ELSE r.term_end END) IS NULL
                    THEN u.created_at
                ELSE (CASE WHEN r.term_end IS NULL THEN r.contract_end ELSE r.term_end + 1 END)
            END AS vacant_from,
            CASE
                WHEN r.contract_rent IS NULL THEN u.unit_base_rent::float
                ELSE r.contract_rent
            END AS rentpm,
            ut.unit_types_name AS unit_type,
            l.locations_name AS locationname,
            mt.management_types_name AS managementtype,
            u.unit_vaccant_status AS unitvacantstatus
        FROM
            units u
        LEFT JOIN
            tenant_contracts tc ON tc.unit_id = u.id
        LEFT JOIN
            unit_types ut ON ut.id = u.unit_type_id
        LEFT JOIN
            termination tr ON tr.contract_id = tc.id
        LEFT JOIN
            buildings b ON b.id = u.building_id
        LEFT JOIN
            management_types mt ON mt.id = b.management_id
        LEFT JOIN
            locations l ON l.id = b.location_id
        LEFT JOIN
            (
                SELECT DISTINCT ON (u.id)
                    tr.id AS trid,
                    u.id AS id,
                    u.unit_no AS unit_no,
                    tc.tenant_contract_no AS contract,
                    tc.tenant_contract_rent AS contract_rent,
                    tc.tenant_contract_valid_to_date AS contract_end,
                    tr.termination_date AS term_end
                FROM
                    termination tr
                LEFT JOIN
                    tenant_contracts tc ON tc.id = tr.contract_id
                INNER JOIN
                    units u ON tc.unit_id = u.id
                LEFT JOIN
                    buildings b ON b.id = tc.building_id
                WHERE
                    tr.work_flow_processes_code = '505'
                ORDER BY
                    u.id, tr.termination_date DESC, tr.id DESC
            ) r ON r.id = tc.unit_id
        WHERE
            u.unit_vaccant_status = '0'
            AND unit_status = '1'
            AND b.building_status = '1'
        ORDER BY
            u.id, r.term_end,b.building_name DESC
    ) AS bom
	 where (bom.vacant_from <= Date2)
	 and (bom.buildingname = building_name or bom.managementtype = mngnt_type)

	),

    b as
	(
      SELECT * FROM a
	)

    SELECT b.buildingname::VARCHAR,
           b.unitno::VARCHAR,
           b.unitstatus::INT,
           b.contractend_date::TIMESTAMP WITHOUT TIME ZONE,
		   b.terminationend_date::TIMESTAMP WITHOUT TIME ZONE,
		   b.vacant_from::TIMESTAMP WITHOUT TIME ZONE,
		   b.vacant_to::TIMESTAMP WITHOUT TIME ZONE,
		   b.rentpm::NUMERIC,
		   b.unit_type::VARCHAR,
		   b.locationname::VARCHAR,
		   b.days::NUMERIC,
		   b.buildingno::VARCHAR
    FROM b order by b.buildingname::VARCHAR, b.unitno::VARCHAR;
END;
$function$;
SQL;
    }

    private function originalFunctionsSql(): string
    {
        return <<<'SQL'
CREATE OR REPLACE FUNCTION public.vacant_unit_date(date2 date)
 RETURNS TABLE(bldgname character varying, untno character varying, untstats integer, contract_end_date timestamp without time zone, termination_end_date timestamp without time zone, vacant_from_date timestamp without time zone, vacant_to_date timestamp without time zone, rent_pm numeric, unittype character varying, location_name character varying, days_btwn numeric, bldg_no character varying)
 LANGUAGE plpgsql
AS $function$BEGIN
  RETURN QUERY
    WITH
	a as
	(select  bom.buildingname,bom.buildingno,bom.unitno,bom.unitstatus,bom.terminationend_date,
	    bom.vacant_from,Date2 as vacant_to,bom.rentpm,bom.unit_type,bom.locationname,bom.contractend_date,
	    (SELECT DATE_PART('day', Date2::timestamp - bom.vacant_from))
		as days,
		bom.managementtype
		FROM
    (
        SELECT DISTINCT ON (u.id)
            r.contract,
            b.building_name AS buildingname,
            b.building_no AS buildingno,
            u.unit_vaccant_status AS unitstatus,
            r.id AS unitid,
            CASE
                WHEN r.unit_no IS NOT NULL THEN r.unit_no
                ELSE u.unit_no
            END AS unitno,
            r.contract_end AS contractend_date,
            r.term_end AS terminationend_date,
            CASE
                WHEN (CASE WHEN r.term_end IS NULL THEN r.contract_end ELSE r.term_end END) IS NULL
                    THEN u.created_at
                ELSE (CASE WHEN r.term_end IS NULL THEN r.contract_end ELSE r.term_end + 1 END)
            END AS vacant_from,
            CASE
                WHEN r.contract_rent IS NULL THEN u.unit_base_rent::float
                ELSE r.contract_rent
            END AS rentpm,
            ut.unit_types_name AS unit_type,
            l.locations_name AS locationname,
            mt.management_types_name AS managementtype,
            u.unit_vaccant_status AS unitvacantstatus
        FROM
            units u
        LEFT JOIN
            tenant_contracts tc ON tc.unit_id = u.id
        LEFT JOIN
            unit_types ut ON ut.id = u.unit_type_id
        LEFT JOIN
            termination tr ON tr.contract_id = tc.id
        LEFT JOIN
            buildings b ON b.id = u.building_id
        LEFT JOIN
            management_types mt ON mt.id = b.management_id
        LEFT JOIN
            locations l ON l.id = b.location_id
        LEFT JOIN
            (
                SELECT
                    MAX(tr.id) AS trid,
                    u.id AS id,
                    MAX(u.unit_no) AS unit_no,
                    MAX(tc.tenant_contract_no) AS contract,
                    MAX(tc.tenant_contract_rent) AS contract_rent,
                    MAX(tenant_contract_valid_to_date) AS contract_end,
                    MAX(tr.termination_date) AS term_end
                FROM
                    termination tr
                LEFT JOIN
                    tenant_contracts tc ON tc.id = tr.contract_id
                INNER JOIN
                    units u ON tc.unit_id = u.id
                LEFT JOIN
                    buildings b ON b.id = tc.building_id
                WHERE
                    tr.work_flow_processes_code = '505'
                GROUP BY
                    u.id
            ) r ON r.id = tc.unit_id
        WHERE
            u.unit_vaccant_status = '0'
            AND unit_status = '1'
            AND b.building_status = '1'
        ORDER BY
            u.id, r.term_end,b.building_name DESC
    ) AS bom
	 where (bom.vacant_from <= Date2)


	),

    b as
	(
      SELECT * FROM a
	)

    SELECT b.buildingname::VARCHAR,
           b.unitno::VARCHAR,
           b.unitstatus::INT,
           b.contractend_date::TIMESTAMP WITHOUT TIME ZONE,
		   b.terminationend_date::TIMESTAMP WITHOUT TIME ZONE,
		   b.vacant_from::TIMESTAMP WITHOUT TIME ZONE,
		   b.vacant_to::TIMESTAMP WITHOUT TIME ZONE,
		   b.rentpm::NUMERIC,
		   b.unit_type::VARCHAR,
		   b.locationname::VARCHAR,
		   b.days::NUMERIC,
		   b.buildingno::VARCHAR
    FROM b order by b.buildingname::VARCHAR, b.unitno::VARCHAR;
END;
$function$;

CREATE OR REPLACE FUNCTION public.vacant_unit_compo(date2 date, building_name character varying, mngnt_type character varying)
 RETURNS TABLE(bldgname character varying, untno character varying, untstats integer, contract_end_date timestamp without time zone, termination_end_date timestamp without time zone, vacant_from_date timestamp without time zone, vacant_to_date timestamp without time zone, rent_pm numeric, unittype character varying, location_name character varying, days_btwn numeric, bldg_no character varying)
 LANGUAGE plpgsql
AS $function$BEGIN
  RETURN QUERY
    WITH
	a as
	(select  bom.buildingname,bom.buildingno,bom.unitno,bom.unitstatus,bom.terminationend_date,
	    bom.vacant_from,Date2 as vacant_to,bom.rentpm,bom.unit_type,bom.locationname,bom.contractend_date,
	    (SELECT DATE_PART('day', Date2::timestamp - bom.vacant_from))
		as days,
		bom.managementtype
		FROM
    (
        SELECT DISTINCT ON (u.id)
            r.contract,
            b.building_name AS buildingname,
            b.building_no AS buildingno,
            u.unit_vaccant_status AS unitstatus,
            r.id AS unitid,
            CASE
                WHEN r.unit_no IS NOT NULL THEN r.unit_no
                ELSE u.unit_no
            END AS unitno,
            r.contract_end AS contractend_date,
            r.term_end AS terminationend_date,
            CASE
                WHEN (CASE WHEN r.term_end IS NULL THEN r.contract_end ELSE r.term_end END) IS NULL
                    THEN u.created_at
                ELSE (CASE WHEN r.term_end IS NULL THEN r.contract_end ELSE r.term_end + 1 END)
            END AS vacant_from,
            CASE
                WHEN r.contract_rent IS NULL THEN u.unit_base_rent::float
                ELSE r.contract_rent
            END AS rentpm,
            ut.unit_types_name AS unit_type,
            l.locations_name AS locationname,
            mt.management_types_name AS managementtype,
            u.unit_vaccant_status AS unitvacantstatus
        FROM
            units u
        LEFT JOIN
            tenant_contracts tc ON tc.unit_id = u.id
        LEFT JOIN
            unit_types ut ON ut.id = u.unit_type_id
        LEFT JOIN
            termination tr ON tr.contract_id = tc.id
        LEFT JOIN
            buildings b ON b.id = u.building_id
        LEFT JOIN
            management_types mt ON mt.id = b.management_id
        LEFT JOIN
            locations l ON l.id = b.location_id
        LEFT JOIN
            (
                SELECT
                    MAX(tr.id) AS trid,
                    u.id AS id,
                    MAX(u.unit_no) AS unit_no,
                    MAX(tc.tenant_contract_no) AS contract,
                    MAX(tc.tenant_contract_rent) AS contract_rent,
                    MAX(tenant_contract_valid_to_date) AS contract_end,
                    MAX(tr.termination_date) AS term_end
                FROM
                    termination tr
                LEFT JOIN
                    tenant_contracts tc ON tc.id = tr.contract_id
                INNER JOIN
                    units u ON tc.unit_id = u.id
                LEFT JOIN
                    buildings b ON b.id = tc.building_id
                WHERE
                    tr.work_flow_processes_code = '505'
                GROUP BY
                    u.id
            ) r ON r.id = tc.unit_id
        WHERE
            u.unit_vaccant_status = '0'
            AND unit_status = '1'
            AND b.building_status = '1'
        ORDER BY
            u.id, r.term_end,b.building_name DESC
    ) AS bom
	 where (bom.vacant_from <= Date2)
	 and (bom.buildingname = building_name or bom.managementtype = mngnt_type)

	),

    b as
	(
      SELECT * FROM a
	)

    SELECT b.buildingname::VARCHAR,
           b.unitno::VARCHAR,
           b.unitstatus::INT,
           b.contractend_date::TIMESTAMP WITHOUT TIME ZONE,
		   b.terminationend_date::TIMESTAMP WITHOUT TIME ZONE,
		   b.vacant_from::TIMESTAMP WITHOUT TIME ZONE,
		   b.vacant_to::TIMESTAMP WITHOUT TIME ZONE,
		   b.rentpm::NUMERIC,
		   b.unit_type::VARCHAR,
		   b.locationname::VARCHAR,
		   b.days::NUMERIC,
		   b.buildingno::VARCHAR
    FROM b order by b.buildingname::VARCHAR, b.unitno::VARCHAR;
END;
$function$;
SQL;
    }
}
