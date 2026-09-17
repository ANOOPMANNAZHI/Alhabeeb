<?php

namespace Modules\BackOffice\Services;

use Modules\BackOffice\Services\TerminationDuesCategory as Cat;

/**
 * Turns the termination figures into the owed lines of a dues record.
 *
 * Rules (see the design spec):
 *   rent        = inspector's "Rent" other-charge row if present, else the
 *                 computed outstanding as on the termination date
 *   municipal   = "Muncipal Tax" other-charge row
 *   other       = every "Any Other Charges" / "Others" row
 *   ew          = termination electricity + water total
 *   maintenance = one line per checklist work row, then a negative
 *                 "Maintenance discount" line capped at the maintenance total
 * Lines with amount <= 0 are dropped. Pure: no models, no DB.
 */
class TerminationDuesBuilder
{
    const DECIMALS = 3;

    const OTHER_ROW_RENT      = 'Rent';
    const OTHER_ROW_MUNICIPAL = 'Muncipal Tax'; // spelling as stored by the inspection form
    const OTHER_ROWS_OTHER    = ['Any Other Charges', 'Others'];

    /**
     * @param array $input rent_os, checklist_other[], checklist_works[], elec_water, maintenance_discount
     * @return array lines: category, owner_team, description, source_type, source_id, amount, line_order
     */
    public static function build(array $input)
    {
        $other = isset($input['checklist_other']) && is_array($input['checklist_other']) ? $input['checklist_other'] : [];
        $works = isset($input['checklist_works']) && is_array($input['checklist_works']) ? $input['checklist_works'] : [];

        $lines = [];

        // Rent
        $rentRow = self::findOther($other, [self::OTHER_ROW_RENT]);
        if ($rentRow) {
            $lines[] = self::line(Cat::RENT, 'Outstanding rent', 'termination_checklist', $rentRow['id'], $rentRow['amount']);
        } else {
            $lines[] = self::line(Cat::RENT, 'Outstanding rent', 'computed', null, isset($input['rent_os']) ? $input['rent_os'] : 0);
        }

        // Municipal tax
        $munRow = self::findOther($other, [self::OTHER_ROW_MUNICIPAL]);
        if ($munRow) {
            $lines[] = self::line(Cat::MUNICIPAL, 'Municipal tax', 'termination_checklist', $munRow['id'], $munRow['amount']);
        }

        // Electricity & water
        $lines[] = self::line(Cat::EW, 'Electricity & water', 'termination', null, isset($input['elec_water']) ? $input['elec_water'] : 0);

        // Other charges
        foreach ($other as $row) {
            if (in_array(trim((string) $row['name']), self::OTHER_ROWS_OTHER, true)) {
                $lines[] = self::line(Cat::OTHER, trim((string) $row['name']), 'termination_checklist', $row['id'], $row['amount']);
            }
        }

        // Maintenance
        $maintenanceTotal = 0.0;
        foreach ($works as $row) {
            $l = self::line(Cat::MAINTENANCE, $row['description'], 'termination_checklist', $row['id'], $row['amount']);
            $lines[] = $l;
            if ($l['amount'] > 0) {
                $maintenanceTotal += $l['amount'];
            }
        }
        $discount = self::money(isset($input['maintenance_discount']) ? $input['maintenance_discount'] : 0);
        if ($discount > 0 && $maintenanceTotal > 0) {
            $lines[] = self::line(Cat::MAINTENANCE, 'Maintenance discount', 'termination', null, -min($discount, $maintenanceTotal), true);
        }

        // Drop empties, number the rest
        $out = [];
        $order = 0;
        foreach ($lines as $l) {
            if ($l['amount'] <= 0 && !$l['_keep_negative']) {
                continue;
            }
            unset($l['_keep_negative']);
            $l['line_order'] = ++$order;
            $out[] = $l;
        }

        // A discount alone is not a debt
        if (count($out) === 1 && $out[0]['amount'] < 0) {
            return [];
        }
        return $out;
    }

    public static function total(array $lines)
    {
        $t = 0.0;
        foreach ($lines as $l) {
            $t += (float) $l['amount'];
        }
        return round($t, self::DECIMALS);
    }

    private static function line($category, $description, $sourceType, $sourceId, $amount, $keepNegative = false)
    {
        return [
            'category'       => $category,
            'owner_team'     => Cat::ownerFor($category),
            'description'    => trim((string) $description) !== '' ? trim((string) $description) : Cat::label($category),
            'source_type'    => $sourceType,
            'source_id'      => $sourceId !== null ? (int) $sourceId : null,
            'amount'         => self::money($amount),
            '_keep_negative' => $keepNegative,
        ];
    }

    private static function findOther(array $rows, array $names)
    {
        foreach ($rows as $row) {
            if (in_array(trim((string) $row['name']), $names, true) && self::money($row['amount']) > 0) {
                return $row;
            }
        }
        return null;
    }

    /** Accepts "1,234.567" strings as the forms post them. */
    private static function money($value)
    {
        if (is_string($value)) {
            $value = str_replace(',', '', $value);
        }
        return round((float) $value, self::DECIMALS);
    }
}
