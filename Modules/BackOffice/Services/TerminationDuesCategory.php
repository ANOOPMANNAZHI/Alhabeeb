<?php

namespace Modules\BackOffice\Services;

/**
 * Dues categories, who follows each one up, and how an account code on a
 * general receipt or deposit-refund line maps to a category.
 * Pure PHP (no DB); the account map itself is read from the `configuration`
 * table by the caller (key SETTING_KEY) and parsed with parseMap().
 */
class TerminationDuesCategory
{
    const RENT        = 'rent';
    const MUNICIPAL   = 'municipal';
    const EW          = 'ew';
    const MAINTENANCE = 'maintenance';
    const OTHER       = 'other';

    const TEAM_BACKOFFICE  = 'backoffice';
    const TEAM_MAINTENANCE = 'maintenance';

    const SETTING_KEY = 'termination_dues_account_map';

    public static function all()
    {
        return [self::RENT, self::MUNICIPAL, self::EW, self::MAINTENANCE, self::OTHER];
    }

    public static function label($category)
    {
        $labels = [
            self::RENT        => 'Rent',
            self::MUNICIPAL   => 'Municipal tax',
            self::EW          => 'Electricity & water',
            self::MAINTENANCE => 'Maintenance',
            self::OTHER       => 'Other charges',
        ];
        return isset($labels[$category]) ? $labels[$category] : ucfirst((string) $category);
    }

    public static function teamLabel($team)
    {
        return $team === self::TEAM_MAINTENANCE ? 'Maintenance' : 'Back Office';
    }

    public static function ownerFor($category)
    {
        return $category === self::MAINTENANCE ? self::TEAM_MAINTENANCE : self::TEAM_BACKOFFICE;
    }

    /** Account codes seen on live deduction / general-receipt lines. */
    public static function defaultAccountMap()
    {
        return [
            self::RENT        => ['12211', '31001'],
            self::MUNICIPAL   => ['41102', '41103'],
            self::EW          => ['41105', '22305'],
            self::MAINTENANCE => ['41110', '22310', '41107', '22307', '12302'],
        ];
    }

    /**
     * @param string|null $json value of the configuration setting
     * @return array category => [codes]
     */
    public static function parseMap($json)
    {
        $decoded = json_decode((string) $json, true);
        if (!is_array($decoded) || empty($decoded)) {
            return self::defaultAccountMap();
        }
        $map = [];
        foreach ($decoded as $category => $codes) {
            if (!in_array($category, self::all(), true) || !is_array($codes)) {
                continue;
            }
            $map[$category] = array_values(array_map(function ($c) { return trim((string) $c); }, $codes));
        }
        return empty($map) ? self::defaultAccountMap() : $map;
    }

    /**
     * @return string|null category, or null when the code is not mapped
     */
    public static function fromAccountCode($code, array $map)
    {
        $code = trim((string) $code);
        if ($code === '') {
            return null;
        }
        foreach ($map as $category => $codes) {
            if (in_array($code, array_map('strval', $codes), true)) {
                return $category;
            }
        }
        return null;
    }
}
