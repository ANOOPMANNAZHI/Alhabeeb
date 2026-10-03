<?php

namespace Modules\BackOffice\Services;

/**
 * Rent Receivable V2 report rows from RentDues::forDate() (amounts) and
 * RentDues::details() (payment mode, contacts, management, ARE, closed cheques),
 * with the field names the report's PDF and Excel already use. Every contract
 * with a balance, sorted by building, unit and contract - the same amounts as
 * the Tenant Aging Report. Pure PHP; PHP < 7.4 in production: no arrow functions.
 */
class ReceivableReportRows
{
    /**
     * @param  object[] $dues     RentDues::forDate() result, keyed by contract id
     * @param  object[] $details  RentDues::details() result, keyed by contract id
     * @return object[]
     */
    public static function build(array $dues, array $details)
    {
        $rows = [];
        foreach ($dues as $id => $d) {
            $l = $d->ledger;
            if ($l['balance'] <= 0) {
                continue;
            }
            $x = isset($details[$id]) ? $details[$id] : null;
            $rows[] = (object) [
                'termination_date' => $d->term_date,
                'tenant_name'      => $d->tenant_name,
                'contract_no'      => $d->contract_no,
                'unit_code'        => $d->unit_code,
                'start_date'       => self::day($d->eff),
                'end_date'         => self::day($d->vto),
                'paymentmode'      => $x ? (string) $x->paymentmode : '',
                'lastpaid_till'    => self::day($d->paid_till),
                'receipt_date'     => self::day($d->last_received),
                'rentper_month'    => (float) $d->rent,
                'contract_value'   => (float) str_replace(',', '', (string) $d->value),
                'amount_received'  => $l['received'],
                'totaldue'         => $l['due'],
                'netamtdue'        => $l['balance'],
                'pdc'              => $d->pdc,
                'pdc_closed'       => $x ? $x->pdc_closed : null,
                'contact_person'   => $x ? $x->contact_person : null,
                'contact_no'       => $x ? $x->contact_no : null,
                'buildingname'     => $d->building_name,
                'building_no'      => $d->building_no,
                'management_type'  => $x ? $x->management_type : null,
                'employee_name'    => $x ? $x->employee_name : null,
            ];
        }
        usort($rows, function ($a, $b) {
            $cmp = strcmp((string) $a->buildingname, (string) $b->buildingname);
            if ($cmp === 0) {
                $cmp = strnatcasecmp((string) $a->unit_code, (string) $b->unit_code);
            }
            return $cmp !== 0 ? $cmp : strcmp((string) $a->contract_no, (string) $b->contract_no);
        });
        return $rows;
    }

    private static function day($value)
    {
        return $value ? substr((string) $value, 0, 10) : null;
    }
}
