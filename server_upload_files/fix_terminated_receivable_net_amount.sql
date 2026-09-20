CREATE OR REPLACE FUNCTION public.tenantrentreceivable(date2 date)
 RETURNS TABLE(termination_date date, tenant_name text, contract_no character varying, unit_code character varying, start_date date, end_date date, paymentmode text, lastpaid_till date, receipt_date date, rentper_month numeric, contract_value numeric, amount_received numeric, pdc text, pdc_closed text, contact_person text, netamtdue numeric, totaldue numeric, contact_no character varying, buildingname text)
 LANGUAGE plpgsql
AS $function$DECLARE
tc_id	numeric;  
total_due          numeric;
received_amt       numeric;
terminationtype    numeric;
enddate            date;
dom                RECORD;
bom 	      RECORD;  

BEGIN                          

FOR bom IN  
SELECT * FROM
(SELECT distinct tc.id,tc.tenant_contract_status,tc.tenant_contract_no as contractno,t.tenant_name as tenantname,t.tenant_contact_person as contactperson,
t.tenant_contact_no as contactno,mt.management_types_name as management_type,e.employee_name,b.building_name as bldg_name,
b.building_no,b.building_status,u.unit_no as unitcode,rg.receipts_generation_type,
case when tc.tenant_contract_payment_type = '1' then 'Monthly'    
when tc.tenant_contract_payment_type = '2' then 'Bi-Monthly'
when tc.tenant_contract_payment_type = '3' then 'Quaterly'
when tc.tenant_contract_payment_type = '4' then  'Half Yearly'
when tc.tenant_contract_payment_type = '5' then 'Yearly'
end as payment_meth,case when tc.pdc_check = 1 then 'Full' when tc.pdc_check = 2 then 'Partial' end as PDC,pdc.nopdc_clsd as pdc_clsd,
tc.tenant_contract_effective_date as startdate,tc.tenant_contract_valid_to_date as enddate,
ter.termination_date terminateddate,f.lastpaiddate as lastpaidtill,f.receipt_tilldate as receiptdate,tc.tenant_contract_rent as rentpermonth,tc.tenant_contract_value as contractvalue,f.receivedamt  as received_amt,h.months,(h.months*tc.tenant_contract_rent) as total_rent,
case when rg.receipts_generation_type is null then (h.months * tc.tenant_contract_rent) else
((h.months * tc.tenant_contract_rent)-f.receivedamt) end as netamtdue
from tenant_contracts tc
left join termination tr on tc.id = tr.contract_id
left join receipts_generation rg on rg.tenant_contract_id = tc.id
left join units u on u.id = tc.unit_id
left join buildings b on b.id = tc.building_id
left join tenant t on t.id = tc.tenant_id
left join management_types mt on mt.id = b.management_id
left join (SELECT pb.building_id,employee_name from employees emp
  left join users us on emp.id = us.user_type_id
  left join are_buildings are on us.id=are.user_id
  left join preferred_buildings pb on are.id=pb.are_building_id
  where pb.assign_to is null)e on e.building_id = tc.building_id
left join (select tc.id,max(ter.termination_date) as termination_date from termination ter
inner join tenant_contracts tc on tc.id = ter.contract_id
   where ter.work_flow_processes_code = '505' group by tc.id) as ter on t.id=tc.id
left join (select distinct tc.id,  
	case when (DATE_PART('month', tr.termination_date::date) = DATE_PART('month',tenant_contract_valid_to_date::date)) AND (DATE_PART('year', tr.termination_date::date) = DATE_PART('year',tenant_contract_valid_to_date::date)) then 
		    (DATE_PART('year', tr.termination_date::date) - DATE_PART('year', tenant_contract_effective_date::date)) * 12 +
            (DATE_PART('month', tr.termination_date::date) - DATE_PART('month',tenant_contract_effective_date::date))
       else (DATE_PART('year', Date2::date) - DATE_PART('year', tenant_contract_effective_date::date)) * 12 +
            (DATE_PART('month', Date2::date) - DATE_PART('month',tenant_contract_effective_date::date))+1
  end as months from tenant_contracts tc
  left join termination tr on tr.contract_id = tc.id) h on h.id = tc.id
left  join (select tc.id,max(receipts_generation_eff_to) as lastpaiddate,max(receipts_generation_receipt_date) as receipt_tilldate,
sum(receipts_generation_amt) as receivedamt from receipts_generation rg
  inner join tenant_contracts tc on tc.id = rg.tenant_contract_id
where rg.receipts_generation_type = '0' and rg.deleted_at is null group by tc.id) as f on f.id = tc.id
left  join (select tc.id,count(rg.id) as nopdc_clsd from receipts_generation rg
      inner join tenant_contracts tc on rg.tenant_contract_id = tc.id
    left outer join pdc p on p.tenant_contract_id = rg.tenant_contract_id
    where p.pdc_cancel_reason = '1' and rg.receipts_generation_payment_method = '1'
    group by tc.id) pdc on pdc.id = tc.id
where tc.tenant_contract_status = '1' and b.building_status = '1'
UNION 
SELECT distinct U.*,h.days,
case when u.terminateddate = u.enddate then u.contractvalue::float 
else round(h.days * (U.rentpermonth/30))end as total_rent,
GREATEST((case when u.terminateddate = u.enddate then u.contractvalue::float
else round(h.days * (U.rentpermonth/30)) end) - COALESCE(U.received_amt,0), 0) as netamtdue
from
(SELECT distinct tc.id,tc.tenant_contract_status,tc.tenant_contract_no as contractno,t.tenant_name as tenantname,t.tenant_contact_person as contactperson,
t.tenant_contact_no as contactno,mt.management_types_name as management_type,e.employee_name,b.building_name as bldg_name,
b.building_no,b.building_status,u.unit_no as unitcode,rg.receipts_generation_type,
case when tc.tenant_contract_payment_type = '1' then 'Monthly'    
when tc.tenant_contract_payment_type = '2' then 'Bi-Monthly'
when tc.tenant_contract_payment_type = '3' then 'Quaterly'
when tc.tenant_contract_payment_type = '4' then  'Half Yearly'
when tc.tenant_contract_payment_type = '5' then 'Yearly'
end as payment_meth,case when tc.pdc_check = 1 then 'Full' when tc.pdc_check = 2 then 'Partial' end as PDC,pdc.nopdc_clsd as pdc_clsd,
tc.tenant_contract_effective_date as startdate,tc.tenant_contract_valid_to_date as enddate,
ter.termination_date as terminateddate,f.lastpaiddate as lastpaidtill,f.receipt_tilldate as receiptdate,tc.tenant_contract_rent as rentpermonth,tc.tenant_contract_value as contractvalue,f.receivedamt  as received_amt
from tenant_contracts tc
left join termination tr on tc.id = tr.contract_id
left join receipts_generation rg on rg.tenant_contract_id = tc.id and rg.receipts_generation_type = 0
left join units u on u.id = tc.unit_id
left join buildings b on b.id = tc.building_id
left join tenant t on t.id = tc.tenant_id
left join management_types mt on mt.id = b.management_id
left join (SELECT pb.building_id,employee_name from employees emp
  left join users us on emp.id = us.user_type_id
  left join are_buildings are on us.id=are.user_id
  left join preferred_buildings pb on are.id=pb.are_building_id
  where pb.assign_to is null)e on e.building_id = tc.building_id
left join (select tc.id,max(ter.termination_date) as termination_date from termination ter
inner join tenant_contracts tc on tc.id = ter.contract_id
   where ter.work_flow_processes_code = '505'group by tc.id) as ter on ter.id=tc.id
left  join (select tc.id,max(receipts_generation_eff_to) as lastpaiddate,max(receipts_generation_receipt_date) as receipt_tilldate,
sum(receipts_generation_amt) as receivedamt from receipts_generation rg
  inner join tenant_contracts tc on tc.id = rg.tenant_contract_id
where rg.receipts_generation_type = '0' and rg.deleted_at is null group by tc.id) as f on f.id = tc.id
left  join (select tc.id,count(rg.id) as nopdc_clsd from receipts_generation rg
      inner join tenant_contracts tc on rg.tenant_contract_id = tc.id
    left outer join pdc p on p.tenant_contract_id = rg.tenant_contract_id
    where p.pdc_cancel_reason = '1' and rg.receipts_generation_payment_method = '1'
    group by tc.id) pdc on pdc.id = tc.id
where ter.termination_date is not null)U
--and tr.os = 'Yes'
left join (
select distinct tenant_contract_no,tc.tenant_contract_duration,tr.termination_date,tenant_contract_effective_date,tenant_contract_valid_to_date, 
		CASE
			WHEN tr.termination_date IS NOT NULL AND (tr.termination_date != tc.tenant_contract_valid_to_date) THEN        
			round(tr.termination_date::date-tenant_contract_start_date::date)-1
		ELSE round(tenant_contract_valid_to_date::date-tenant_contract_start_date::date)-1
        END AS days
  from tenant_contracts tc
  left join termination tr on tr.contract_id = tc.id
  and tr.work_flow_processes_code = '505'
  ) h on h.tenant_contract_no = U.contractno	   
UNION
SELECT distinct tc.id,tc.tenant_contract_status,tc.tenant_contract_no as contractno,t.tenant_name as tenantname,t.tenant_contact_person as contactperson,
t.tenant_contact_no as contactno,mt.management_types_name as management_type,e.employee_name,b.building_name as bldg_name,
b.building_no,b.building_status,u.unit_no as unitcode,rg.receipts_generation_type,
case when tc.tenant_contract_payment_type = '1' then 'Monthly'    
when tc.tenant_contract_payment_type = '2' then 'Bi-Monthly'
when tc.tenant_contract_payment_type = '3' then 'Quaterly'
when tc.tenant_contract_payment_type = '4' then  'Half Yearly'
when tc.tenant_contract_payment_type = '5' then 'Yearly'
end as payment_meth,case when tc.pdc_check = 1 then 'Full' when tc.pdc_check = 2 then 'Partial' end as PDC,pdc.nopdc_clsd as pdc_clsd,
tc.tenant_contract_effective_date as startdate,tc.tenant_contract_valid_to_date as enddate,
ter.termination_date as terminateddate,f.lastpaiddate as lastpaidtill,f.receipt_tilldate as receiptdate,
tc.tenant_contract_rent as rentpermonth,tc.tenant_contract_value as contractvalue,
case when f.receivedamt is null then f.receivedamt else f.receivedamt end as received_amt,tc.tenant_contract_duration::double precision,CAST (tc.tenant_contract_value AS float) as total_rent,
case when rg.receipts_generation_type is null then (tc.tenant_contract_duration::double precision * tc.tenant_contract_rent) else
(CAST (tc.tenant_contract_value AS float)-f.receivedamt) end as netamtdue
from tenant_contracts tc
left join termination tr on tc.id = tr.contract_id
left join receipts_generation rg on rg.tenant_contract_id = tc.id and rg.receipts_generation_type = 0
left join units u on u.id = tc.unit_id
left join buildings b on b.id = tc.building_id
left join tenant t on t.id = tc.tenant_id
left join management_types mt on mt.id = b.management_id
left join (SELECT pb.building_id,employee_name from employees emp
  left join users us on emp.id = us.user_type_id
  left join are_buildings are on us.id=are.user_id
  left join preferred_buildings pb on are.id=pb.are_building_id
  where pb.assign_to is null)e on e.building_id = tc.building_id
left join (select distinct tc.id,max(ter.termination_date) as termination_date from termination ter
inner join tenant_contracts tc on tc.id = ter.contract_id
   where ter.work_flow_processes_code = '505'
   group by tc.id ) as ter on ter.id=tc.id
-- left join (select distinct tenant_contract_no,  
--   (DATE_PART('year', tenant_contract_valid_to_date::date) - DATE_PART('year', tenant_contract_effective_date::date)) * 12 +
--             (DATE_PART('month', tenant_contract_valid_to_date::date) - DATE_PART('month', tenant_contract_effective_date::date))+1
--   as months from tenant_contracts) h on h.tenant_contract_no = tc.tenant_contract_no
left  join (select tc.id,max(receipts_generation_eff_to) as lastpaiddate,max(receipts_generation_receipt_date) as receipt_tilldate,
sum(receipts_generation_amt) as receivedamt from receipts_generation rg
  inner join tenant_contracts tc on tc.id = rg.tenant_contract_id
where rg.receipts_generation_type = '0' and rg.deleted_at is null group by tc.id) as f on f.id = tc.id
left  join (select tc.id,count(rg.id) as nopdc_clsd from receipts_generation rg
      inner join tenant_contracts tc on rg.tenant_contract_id = tc.id
    left outer join pdc p on p.tenant_contract_id = rg.tenant_contract_id
    where p.pdc_cancel_reason = '1' and rg.receipts_generation_payment_method = '1'
    group by tc.id) pdc on pdc.id = tc.id
where tc.tenant_contract_status = '0' and b.building_status = '1' and ter.termination_date is null
order by contractno desc)A
where (A.startdate::date <= Date2)
and ( A.receipts_generation_type = '0' or A.receipts_generation_type is null)
and (A.received_amt != A.total_rent or A.received_amt is null)
and (A.netamtdue > '0')
and (A.building_status = '1')
order by A.bldg_name,A.unitcode

LOOP
                 tenant_name := bom.tenantname ;
                 Contract_no := bom.contractno;
                 unit_code :=bom.unitcode;
                 start_date :=bom.startdate;
                 end_date :=bom.enddate;
                 paymentmode :=bom.payment_meth;
                 lastpaid_till :=bom.lastpaidtill;
                 receipt_date :=bom.receiptdate;
                 rentper_month :=bom.rentpermonth;
                 contract_value :=bom.contractvalue;
                 amount_received :=bom.received_amt;
                 pdc :=bom.PDC;
                 pdc_closed :=bom.pdc_clsd;
                 contact_person :=bom.contactperson;
                 netamtdue :=bom.netamtdue;
				 totaldue :=bom.total_rent;
				 contact_no :=bom.contactno;
				 termination_date := bom.terminateddate;
				 buildingname :=bom.bldg_name;

              RETURN NEXT;
   END LOOP;
END;
$function$
;
CREATE OR REPLACE FUNCTION public.tenantrentreceivablecompo(date2 date, tenantnamepara character varying, buildingnamepara character varying, buildingno character varying, managementtypesname text, employeename text)
 RETURNS TABLE(termination_date date, tenant_name text, contract_no character varying, unit_code character varying, start_date date, end_date date, paymentmode text, lastpaid_till date, receipt_date date, rentper_month numeric, contract_value numeric, amount_received numeric, pdc text, pdc_closed text, contact_person text, netamtdue numeric, totaldue numeric, contact_no character varying, buildingname text, building_no character varying, management_type text)
 LANGUAGE plpgsql
 ROWS 10000
AS $function$DECLARE
tc_id numeric;  
total_due          numeric;
received_amt       numeric;
terminationtype    numeric;
enddate            date;
dom                RECORD;
bom      RECORD;  

BEGIN                          

FOR bom IN  
 SELECT * FROM
(SELECT distinct tc.id,tc.tenant_contract_status,tc.tenant_contract_no as contractno,t.tenant_name as tenantname,t.tenant_contact_person as contactperson,
t.tenant_contact_no as contactno,mt.management_types_name as management_type,e.employee_name,b.building_name as bldg_name,
b.building_no,u.unit_no as unitcode,
 case when rg.receipts_generation_type = 1 then 0
 when rg.receipts_generation_type = 2 then 0 else rg.receipts_generation_type
end as receipts_generation_type,
case when tc.tenant_contract_payment_type = '1' then 'Monthly'    
when tc.tenant_contract_payment_type = '2' then 'Bi-Monthly'
when tc.tenant_contract_payment_type = '3' then 'Quaterly'
when tc.tenant_contract_payment_type = '4' then  'Half Yearly'
when tc.tenant_contract_payment_type = '5' then 'Yearly'
end as payment_meth,case when tc.pdc_check = 1 then 'Full' when tc.pdc_check = 2 then 'Partial' end as PDC,pdc.nopdc_clsd as pdc_clsd,
tc.tenant_contract_effective_date as startdate,tc.tenant_contract_valid_to_date as enddate,
ter.termination_date terminateddate,f.lastpaiddate as lastpaidtill,f.receipt_tilldate as receiptdate,tc.tenant_contract_rent as rentpermonth,tc.tenant_contract_value as contractvalue,f.receivedamt  as received_amt,h.months,(h.months*tc.tenant_contract_rent) as total_rent,
case when rg.receipts_generation_type is null then (h.months * tc.tenant_contract_rent) else
((h.months * tc.tenant_contract_rent)-COALESCE(f.receivedamt,0)) end as netamtdue
from tenant_contracts tc
left join termination tr on tc.id = tr.contract_id
left join receipts_generation rg on rg.tenant_contract_id = tc.id
left join units u on u.id = tc.unit_id
left join buildings b on b.id = tc.building_id
left join tenant t on t.id = tc.tenant_id
left join management_types mt on mt.id = b.management_id
left join (SELECT pb.building_id,employee_name from employees emp
  left join users us on emp.id = us.user_type_id
  left join are_buildings are on us.id=are.user_id
  left join preferred_buildings pb on are.id=pb.are_building_id
  where pb.assign_to is null)e on e.building_id = tc.building_id
left join (select tc.id,max(ter.termination_date) as termination_date from termination ter
inner join tenant_contracts tc on tc.id = ter.contract_id
   where ter.work_flow_processes_code = '505' group by tc.id) as ter on t.id=tc.id
left join (select distinct tc.id,  
	case when (DATE_PART('month', tr.termination_date::date) = DATE_PART('month',tenant_contract_valid_to_date::date)) AND (DATE_PART('year', tr.termination_date::date) = DATE_PART('year',tenant_contract_valid_to_date::date)) then 
		    (DATE_PART('year', tr.termination_date::date) - DATE_PART('year', tenant_contract_effective_date::date)) * 12 +
            (DATE_PART('month', tr.termination_date::date) - DATE_PART('month',tenant_contract_effective_date::date))
       else (DATE_PART('year', Date2::date) - DATE_PART('year', tenant_contract_effective_date::date)) * 12 +
            (DATE_PART('month', Date2::date) - DATE_PART('month',tenant_contract_effective_date::date))+1
  end as months from tenant_contracts tc
  left join termination tr on tr.contract_id = tc.id) h on h.id = tc.id
left  join (select tc.id,max(receipts_generation_eff_to) as lastpaiddate,max(receipts_generation_receipt_date) as receipt_tilldate,
sum(receipts_generation_amt) as receivedamt from receipts_generation rg
  inner join tenant_contracts tc on tc.id = rg.tenant_contract_id
where rg.receipts_generation_type = '0' and rg.deleted_at is null group by tc.id) as f on f.id = tc.id
left  join (select tc.id,count(rg.id) as nopdc_clsd from receipts_generation rg
      inner join tenant_contracts tc on rg.tenant_contract_id = tc.id
    left outer join pdc p on p.tenant_contract_id = rg.tenant_contract_id
    where p.pdc_cancel_reason = '1' and rg.receipts_generation_payment_method = '1'
    group by tc.id) pdc on pdc.id = tc.id
where tc.tenant_contract_status = '1' 
UNION 
SELECT distinct U.*,h.days,
case when u.terminateddate = u.enddate then u.contractvalue::float 
else round(h.days * (U.rentpermonth/30))end as total_rent,
GREATEST((case when u.terminateddate = u.enddate then u.contractvalue::float
else round(h.days * (U.rentpermonth/30)) end) - COALESCE(U.received_amt,0), 0) as netamtdue
from
(SELECT distinct tc.id,tc.tenant_contract_status,tc.tenant_contract_no as contractno,t.tenant_name as tenantname,t.tenant_contact_person as contactperson,
t.tenant_contact_no as contactno,mt.management_types_name as management_type,e.employee_name,b.building_name as bldg_name,
b.building_no,u.unit_no as unitcode,
  case when rg.receipts_generation_type = 1 then 0
 when rg.receipts_generation_type = 2 then 0 else rg.receipts_generation_type
end as receipts_generation_type,
case when tc.tenant_contract_payment_type = '1' then 'Monthly'    
when tc.tenant_contract_payment_type = '2' then 'Bi-Monthly'
when tc.tenant_contract_payment_type = '3' then 'Quaterly'
when tc.tenant_contract_payment_type = '4' then  'Half Yearly'
when tc.tenant_contract_payment_type = '5' then 'Yearly'
end as payment_meth,case when tc.pdc_check = 1 then 'Full' when tc.pdc_check = 2 then 'Partial' end as PDC,pdc.nopdc_clsd as pdc_clsd,
tc.tenant_contract_effective_date as startdate,tc.tenant_contract_valid_to_date as enddate,
ter.termination_date as terminateddate,f.lastpaiddate as lastpaidtill,f.receipt_tilldate as receiptdate,tc.tenant_contract_rent as rentpermonth,tc.tenant_contract_value as contractvalue,f.receivedamt  as received_amt
from tenant_contracts tc
left join termination tr on tc.id = tr.contract_id
left join receipts_generation rg on rg.tenant_contract_id = tc.id and rg.receipts_generation_type = 0
left join units u on u.id = tc.unit_id
left join buildings b on b.id = tc.building_id
left join tenant t on t.id = tc.tenant_id
left join management_types mt on mt.id = b.management_id
left join (SELECT pb.building_id,employee_name from employees emp
  left join users us on emp.id = us.user_type_id
  left join are_buildings are on us.id=are.user_id
  left join preferred_buildings pb on are.id=pb.are_building_id
  where pb.assign_to is null)e on e.building_id = tc.building_id
left join (select tc.id,max(ter.termination_date) as termination_date from termination ter
inner join tenant_contracts tc on tc.id = ter.contract_id
   where ter.work_flow_processes_code = '505'group by tc.id) as ter on ter.id=tc.id
left  join (select tc.id,max(receipts_generation_eff_to) as lastpaiddate,max(receipts_generation_receipt_date) as receipt_tilldate,
sum(receipts_generation_amt) as receivedamt from receipts_generation rg
  inner join tenant_contracts tc on tc.id = rg.tenant_contract_id
where rg.receipts_generation_type = '0' and rg.deleted_at is null group by tc.id) as f on f.id = tc.id
left  join (select tc.id,count(rg.id) as nopdc_clsd from receipts_generation rg
      inner join tenant_contracts tc on rg.tenant_contract_id = tc.id
    left outer join pdc p on p.tenant_contract_id = rg.tenant_contract_id
    where p.pdc_cancel_reason = '1' and rg.receipts_generation_payment_method = '1'
    group by tc.id) pdc on pdc.id = tc.id
where ter.termination_date is not null)U
--and tr.os = 'Yes'
left join (
select distinct tenant_contract_no,tc.tenant_contract_duration,tr.termination_date,tenant_contract_effective_date,tenant_contract_valid_to_date, 
		CASE
			WHEN tr.termination_date IS NOT NULL AND (tr.termination_date != tc.tenant_contract_valid_to_date) THEN        
			round(tr.termination_date::date-tenant_contract_start_date::date)-1
		ELSE round(tenant_contract_valid_to_date::date-tenant_contract_start_date::date)-1
        END AS days
  from tenant_contracts tc
  left join termination tr on tr.contract_id = tc.id
  and tr.work_flow_processes_code = '505'
  ) h on h.tenant_contract_no = U.contractno	   
UNION
SELECT distinct tc.id,tc.tenant_contract_status,tc.tenant_contract_no as contractno,t.tenant_name as tenantname,t.tenant_contact_person as contactperson,
t.tenant_contact_no as contactno,mt.management_types_name as management_type,e.employee_name,b.building_name as bldg_name,
b.building_no,u.unit_no as unitcode,
  case when rg.receipts_generation_type = 1 then 0
 when rg.receipts_generation_type = 2 then 0 else rg.receipts_generation_type
end as receipts_generation_type,
case when tc.tenant_contract_payment_type = '1' then 'Monthly'    
when tc.tenant_contract_payment_type = '2' then 'Bi-Monthly'
when tc.tenant_contract_payment_type = '3' then 'Quaterly'
when tc.tenant_contract_payment_type = '4' then  'Half Yearly'
when tc.tenant_contract_payment_type = '5' then 'Yearly'
end as payment_meth,case when tc.pdc_check = 1 then 'Full' when tc.pdc_check = 2 then 'Partial' end as PDC,pdc.nopdc_clsd as pdc_clsd,
tc.tenant_contract_effective_date as startdate,tc.tenant_contract_valid_to_date as enddate,
ter.termination_date as terminateddate,f.lastpaiddate as lastpaidtill,f.receipt_tilldate as receiptdate,
tc.tenant_contract_rent as rentpermonth,tc.tenant_contract_value as contractvalue,
case when f.receivedamt is null then f.receivedamt else f.receivedamt end as received_amt,tc.tenant_contract_duration::double precision,CAST (tc.tenant_contract_value AS float) as total_rent,
case when rg.receipts_generation_type is null then (tc.tenant_contract_duration::double precision * tc.tenant_contract_rent) else
(CAST (tc.tenant_contract_value AS float)-COALESCE(f.receivedamt,0)) end as netamtdue
from tenant_contracts tc
left join termination tr on tc.id = tr.contract_id
left join receipts_generation rg on rg.tenant_contract_id = tc.id and rg.receipts_generation_type = 0
left join units u on u.id = tc.unit_id
left join buildings b on b.id = tc.building_id
left join tenant t on t.id = tc.tenant_id
left join management_types mt on mt.id = b.management_id
left join (SELECT pb.building_id,employee_name from employees emp
  left join users us on emp.id = us.user_type_id
  left join are_buildings are on us.id=are.user_id
  left join preferred_buildings pb on are.id=pb.are_building_id
  where pb.assign_to is null)e on e.building_id = tc.building_id
left join (select distinct tc.id,max(ter.termination_date) as termination_date from termination ter
inner join tenant_contracts tc on tc.id = ter.contract_id
   where ter.work_flow_processes_code = '505'
   group by tc.id ) as ter on ter.id=tc.id
-- left join (select distinct tenant_contract_no,  
--   (DATE_PART('year', tenant_contract_valid_to_date::date) - DATE_PART('year', tenant_contract_effective_date::date)) * 12 +
--             (DATE_PART('month', tenant_contract_valid_to_date::date) - DATE_PART('month', tenant_contract_effective_date::date))+1
--   as months from tenant_contracts) h on h.tenant_contract_no = tc.tenant_contract_no
left  join (select tc.id,max(receipts_generation_eff_to) as lastpaiddate,max(receipts_generation_receipt_date) as receipt_tilldate,
sum(receipts_generation_amt) as receivedamt from receipts_generation rg
  inner join tenant_contracts tc on tc.id = rg.tenant_contract_id
where rg.receipts_generation_type = '0' and rg.deleted_at is null group by tc.id) as f on f.id = tc.id
left  join (select tc.id,count(rg.id) as nopdc_clsd from receipts_generation rg
      inner join tenant_contracts tc on rg.tenant_contract_id = tc.id
    left outer join pdc p on p.tenant_contract_id = rg.tenant_contract_id
    where p.pdc_cancel_reason = '1' and rg.receipts_generation_payment_method = '1'
    group by tc.id) pdc on pdc.id = tc.id
where tc.tenant_contract_status = '0' and ter.termination_date is null
order by contractno desc)A
where (A.startdate::date <= Date2)
and ( A.receipts_generation_type = '0' or A.receipts_generation_type is null)
and (A.received_amt != A.total_rent or A.received_amt is null)
and (A.netamtdue >= '30')
and (A.tenantname = tenantnamepara or A.bldg_name = buildingnamepara
    or A.building_no = buildingno or A.management_type = managementtypesname
    or A.employee_name = employeename)
order by A.bldg_name,A.unitcode

LOOP
                 tenant_name := bom.tenantname ;
                 Contract_no := bom.contractno;
                 unit_code :=bom.unitcode;
                 start_date :=bom.startdate;
                 end_date :=bom.enddate;
                 paymentmode :=bom.payment_meth;
                 lastpaid_till :=bom.lastpaidtill;
                 receipt_date :=bom.receiptdate;
                 rentper_month :=bom.rentpermonth;
                 contract_value :=bom.contractvalue;
                 amount_received :=bom.received_amt;
                 pdc :=bom.PDC;
                 pdc_closed :=bom.pdc_clsd;
                 contact_person :=bom.contactperson;
                 netamtdue :=bom.netamtdue;
totaldue :=bom.total_rent;
contact_no :=bom.contactno;
termination_date := bom.terminateddate;
buildingname :=bom.bldg_name;

              RETURN NEXT;
   END LOOP;
END;
$function$
;
