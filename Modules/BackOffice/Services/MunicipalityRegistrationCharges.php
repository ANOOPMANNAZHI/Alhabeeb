<?php

namespace Modules\BackOffice\Services;

/**
 * Arithmetic and General Ledger line shapes for the municipal tax recorded
 * when a tenant contract is registered with the municipality. Pure: no DB.
 * Runs on PHP < 7.4 in production: no arrow functions.
 */
class MunicipalityRegistrationCharges
{
    /** DimDetail id for the PLMS division (manual M.TAX vouchers use it). */
    const DIVISION_DIM_ID = 2;
    const DIM1_TYPE = 'Modules\BackOffice\Entities\DimDetail';
    const DIM2_TYPE = 'Modules\Masters\Entities\Building';

    /** "3,600.500" -> 3600.5; blank / non-numeric -> 0. */
    public static function money($value)
    {
        $clean = str_replace(',', '', trim((string) $value));
        return is_numeric($clean) ? round((float) $clean, 3) : 0.0;
    }

    public static function defaultTax($contractValue, $rate)
    {
        return round(self::money($contractValue) * (float) $rate / 100, 3);
    }

    public static function sameAmount($a, $b)
    {
        return abs(self::money($a) - self::money($b)) < 0.0005;
    }

    public static function description($prefix, $contractNo, $buildingName, $unitNo)
    {
        $parts = [];
        foreach ([$prefix, $contractNo, $buildingName, $unitNo] as $p) {
            $p = trim((string) $p);
            if ($p !== '') {
                $parts[] = $p;
            }
        }
        return implode(' ', $parts);
    }

    /** Debit line then credit line, both on the contract's building and unit. */
    public static function voucherLines($amount, $debitAccountId, $creditAccountId, $buildingId, $unitId)
    {
        $amount = round((float) $amount, 3);
        $make = function ($accountId, $debit, $credit) use ($buildingId, $unitId) {
            return [
                'account_id'    => $accountId,
                'building_id'   => $buildingId,
                'unit_id'       => $unitId,
                'jv_desc'       => null,
                'debit_amt'     => $debit,
                'credit_amt'    => $credit,
                'recovery'      => 0,
                'dim1able_type' => self::DIM1_TYPE,
                'dim1able_id'   => self::DIVISION_DIM_ID,
                'dim2able_type' => self::DIM2_TYPE,
                'dim2able_id'   => $buildingId,
            ];
        };
        return [
            $make($debitAccountId, $amount, 0.0),
            $make($creditAccountId, 0.0, $amount),
        ];
    }
}
