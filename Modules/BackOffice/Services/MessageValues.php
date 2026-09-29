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
            'unit'           => self::v(self::unitNo($unit)),
            'location'       => self::v($location ? $location->locations_name : ''),
            'rent'           => $contract && $contract->tenant_contract_rent !== null ? number_format((float) $contract->tenant_contract_rent, 3) : '-',
            'contract_start' => self::date($contract ? $contract->tenant_contract_start_date : null),
            'contract_end'   => self::date($contract ? $contract->tenant_contract_valid_to_date : null),
            'mobile'         => self::v($tenant ? ($tenant->tenant_contact_no ?: $tenant->tenant_residence_tel) : ''),
        ];
    }

    /** The unit number people use ("84"); unit_code carries the building prefix. */
    private static function unitNo($unit)
    {
        if (!$unit) {
            return '';
        }
        return trim((string) $unit->unit_no) !== '' ? $unit->unit_no : $unit->unit_code;
    }

    public static function tenant($tenant)
    {
        return [
            'tenant_name' => self::v($tenant ? $tenant->tenant_name : ''),
            'mobile'      => self::v($tenant ? ($tenant->tenant_contact_no ?: $tenant->tenant_residence_tel) : ''),
        ];
    }

    public static function complaint($complaint)
    {
        $building = $complaint ? $complaint->building : null;
        $unit = $complaint ? $complaint->Unit : null;
        $location = $complaint ? $complaint->location : null;
        return [
            'complaint_no'   => self::v($complaint ? $complaint->complaint_no : ''),
            'complaint_date' => self::date($complaint ? $complaint->complaint_date : null),
            'building'       => self::v($building ? $building->building_name : ''),
            'unit'           => self::v(self::unitNo($unit)),
            'location'       => self::v($location ? $location->locations_name : ''),
        ] + self::tenant($complaint ? $complaint->tenant : null);
    }

    public static function vendor($vendor)
    {
        return ['landlord_name' => self::v($vendor ? $vendor->vendor_name : '')];
    }

    /** Building and unit by id (key handover and other screens that only hold ids). */
    public static function place($buildingId, $unitId)
    {
        $building = $buildingId ? \Modules\Masters\Entities\Building::find($buildingId) : null;
        $unit = $unitId ? \Modules\Masters\Entities\Unit::find($unitId) : null;
        return [
            'building' => self::v($building ? $building->building_name : ''),
            'unit'     => self::v(self::unitNo($unit)),
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
