<?php

namespace Modules\BackOffice\Services;

/**
 * Which placeholders a template may use, by audience (and, for system
 * templates, by key), with sample values for preview. Pure: no DB.
 */
class MessagePlaceholders
{
    const AUDIENCES = ['tenant' => 'Tenants', 'landlord' => 'Landlords', 'staff' => 'Staff', 'any' => 'Anyone'];

    private static $common = [
        'today'        => ['label' => "Today's date", 'sample' => '29/09/2026'],
        'company_name' => ['label' => 'Company name', 'sample' => 'Al Habib & Co. L.L.C'],
    ];

    private static $byAudience = [
        'tenant' => [
            'tenant_name'    => ['label' => 'Tenant name', 'sample' => 'Mohammed Al Balushi'],
            'contract_no'    => ['label' => 'Contract no.', 'sample' => 'TAG2601977'],
            'building'       => ['label' => 'Building', 'sample' => 'Al Qandeel-2 Bldg'],
            'unit'           => ['label' => 'Unit', 'sample' => '84'],
            'location'       => ['label' => 'Location', 'sample' => 'Al Khuwair'],
            'rent'           => ['label' => 'Monthly rent', 'sample' => '250.000'],
            'contract_start' => ['label' => 'Contract start', 'sample' => '15/09/2026'],
            'contract_end'   => ['label' => 'Contract end', 'sample' => '30/09/2027'],
            'mobile'         => ['label' => 'Tenant mobile', 'sample' => '99123456'],
        ],
        'landlord' => [
            'landlord_name' => ['label' => 'Landlord name', 'sample' => 'Ali Mallalah Habib'],
            'building'      => ['label' => 'Building', 'sample' => 'Rex Bldg'],
            'location'      => ['label' => 'Location', 'sample' => 'Ruwi'],
        ],
        'staff' => [
            'employee_name' => ['label' => 'Employee name', 'sample' => 'Anoop'],
            'role'          => ['label' => 'Role', 'sample' => 'Back Office Manager'],
        ],
        'any' => [],
    ];

    /** @var array system template key => extra placeholders (registered by project C) */
    private static $system = [];

    public static function registerSystem($key, array $extra)
    {
        self::$system[$key] = $extra;
    }

    /**
     * A system template (registered key) offers the common placeholders plus
     * exactly the ones its send site fills; other templates get the audience set.
     */
    public static function forTemplate($audience, $key = null)
    {
        if ($key !== null && isset(self::$system[$key])) {
            return self::$common + self::$system[$key];
        }
        $audience = isset(self::$byAudience[$audience]) ? $audience : 'any';
        return self::$common + self::$byAudience[$audience];
    }

    /** Sample values for text placeholders (blocks excluded). */
    public static function samples($audience, $key = null)
    {
        $out = [];
        foreach (self::forTemplate($audience, $key) as $name => $p) {
            if (empty($p['block'])) {
                $out[$name] = (string) $p['sample'];
            }
        }
        return $out;
    }

    /** Sample HTML for block placeholders (tables built in code). */
    public static function blockSamples($audience, $key = null)
    {
        $out = [];
        foreach (self::forTemplate($audience, $key) as $name => $p) {
            if (!empty($p['block'])) {
                $out[$name] = (string) $p['sample'];
            }
        }
        return $out;
    }
}
