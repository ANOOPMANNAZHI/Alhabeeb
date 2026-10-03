<?php

namespace Modules\BackOffice\Services;

use Carbon\Carbon;

/**
 * Tenant Aging Report: how long each contract's unpaid rent has been due.
 * Works on RentLedger lines: receipts have already cleared the oldest lines,
 * so every unpaid line is aged by the days since its due date (as-on date -
 * due date) into fixed day windows. The buckets always add up to the balance.
 * Pure PHP (no DB); PHP < 7.4 in production: no arrow functions.
 */
class TenantAgingCalculator
{
    /** key => [label, min days overdue, max days overdue (null = open ended)] */
    const BUCKETS = [
        'current'  => ['0 - 30',    0,   30],
        'b31_60'   => ['31 - 60',   31,  60],
        'b61_90'   => ['61 - 90',   61,  90],
        'b91_120'  => ['91 - 120',  91,  120],
        'b121_180' => ['121 - 180', 121, 180],
        'b180plus' => ['180 +',     181, null],
    ];

    /** @return array key => ['label', 'from' (Y-m-d|null), 'to' (Y-m-d)] */
    public static function buckets($asOn)
    {
        $asOn = Carbon::parse($asOn)->startOfDay();
        $out = [];
        foreach (self::BUCKETS as $key => $def) {
            $out[$key] = [
                'label' => $def[0],
                'from'  => $def[2] === null ? null : $asOn->copy()->subDays($def[2])->toDateString(),
                'to'    => $asOn->copy()->subDays($def[1])->toDateString(),
            ];
        }
        return $out;
    }

    /**
     * @param  array  $lines RentLedger lines (due_date, amount, unpaid)
     * @return array  bucket key => unpaid amount (3 dp)
     */
    public static function age(array $lines, $asOn)
    {
        $asOn = Carbon::parse($asOn)->startOfDay();
        $amounts = array_fill_keys(array_keys(self::BUCKETS), 0.0);
        foreach ($lines as $l) {
            if ($l['unpaid'] <= 0) {
                continue;
            }
            $days = Carbon::parse($l['due_date'])->startOfDay()->diffInDays($asOn, false);
            $amounts[self::bucketFor(max(0, $days))] += $l['unpaid'];
        }
        foreach ($amounts as $k => $v) {
            $amounts[$k] = round($v, 3);
        }
        return $amounts;
    }

    public static function bucketFor($days)
    {
        foreach (self::BUCKETS as $key => $def) {
            if ($days >= $def[1] && ($def[2] === null || $days <= $def[2])) {
                return $key;
            }
        }
        return 'b180plus';
    }

    /**
     * Report rows from RentDues::forDate(): every contract with a balance, sorted by
     * building, unit and contract, with the field names the PDF and Excel use.
     */
    public static function rows(array $dues, $asOn)
    {
        $rows = [];
        foreach ($dues as $d) {
            if ($d->ledger['balance'] <= 0) {
                continue;
            }
            $rows[] = (object) [
                'buildingname' => $d->building_name,
                'tenant_name'  => $d->tenant_name,
                'contract_no'  => $d->contract_no,
                'unit_code'    => $d->unit_code,
                'pdc'          => $d->pdc,
                'netamtdue'    => $d->ledger['balance'],
                'aging'        => self::age($d->ledger['lines'], $asOn),
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
}
