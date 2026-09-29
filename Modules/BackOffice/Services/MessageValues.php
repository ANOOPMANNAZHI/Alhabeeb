<?php

namespace Modules\BackOffice\Services;

/** Placeholder values from PLMS records, so send sites stay short. Missing data becomes "-". */
class MessageValues
{
    private static function v($x)
    {
        $x = trim((string) $x);
        return $x === '' ? '-' : $x;
    }

    private static function date($d)
    {
        return $d ? date('d/m/Y', strtotime($d)) : '-';
    }

    public static function contract($contract)
    {
        $tenant = $contract ? $contract->tenant : null;
        $building = $contract ? $contract->building : null;
        $unit = $contract ? $contract->unit : null;
        $location = $building ? $building->location : null;
        return [
            'tenant_name'    => self::v($tenant ? $tenant->tenant_name : ''),
            'contract_no'    => self::v($contract ? $contract->tenant_contract_no : ''),
            'building'       => self::v($building ? $building->building_name : ''),
            'unit'           => self::v($unit ? $unit->unit_code : ''),
            'location'       => self::v($location ? $location->locations_name : ''),
            'rent'           => $contract && $contract->tenant_contract_rent !== null ? number_format((float) $contract->tenant_contract_rent, 3) : '-',
            'contract_start' => self::date($contract ? $contract->tenant_contract_start_date : null),
            'contract_end'   => self::date($contract ? $contract->tenant_contract_valid_to_date : null),
            'mobile'         => self::v($tenant ? ($tenant->tenant_contact_no ?: $tenant->tenant_residence_tel) : ''),
        ];
    }

    public static function employee($user)
    {
        $employee = $user ? $user->employee : null;
        $role = $user && method_exists($user, 'getRoleNames') ? $user->getRoleNames()->first() : '';
        return [
            'employee_name' => self::v($employee && $employee->exists && $employee->employee_name ? $employee->employee_name : ($user ? $user->username : '')),
            'role'          => self::v($role ? ucwords(str_replace('_', ' ', $role)) : ''),
        ];
    }

    public static function cheque($pdc)
    {
        return [
            'cheque_no'     => self::v($pdc->pdc_check_no),
            'amount'        => $pdc->pdc_amt !== null ? number_format((float) $pdc->pdc_amt, 3) : '-',
            'cheque_date'   => self::date($pdc->pdc_check_date),
            'bounce_reason' => self::v($pdc->pdc_cancel_reason_name),
            'remarks'       => self::v(isset($pdc->bounce_remark) ? $pdc->bounce_remark : ''),
        ];
    }
}
