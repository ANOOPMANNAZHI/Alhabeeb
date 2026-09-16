<?php

namespace Modules\BackOffice\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Modules\Masters\Entities\Building;
use Modules\Sales\Entities\LandlordContract;
use Modules\BackOffice\Services\LandlordContractFeeRuleParser;

/**
 * Loads management fees + cleaning charges from the "NM Buildings Fees &
 * Cleaning Expenses" sheet (frozen in data/landlord_contract_fees.php) into
 * every APPROVED landlord contract of each mapped building.
 *
 *   Dry run (default, writes nothing):
 *     php artisan db:seed --class="Modules\BackOffice\Database\Seeders\LandlordContractFeesSeeder"
 *   Apply:
 *     LC_FEES_APPLY=1 php artisan db:seed --class="Modules\BackOffice\Database\Seeders\LandlordContractFeesSeeder"
 *     (Windows: set LC_FEES_APPLY=1 && php artisan db:seed --class=...)
 *
 * Before writing, the previous values are saved to
 * storage/app/landlord_contract_fees_backup_<timestamp>.json. Re-running after
 * apply reports 0 changes. Intentionally NOT called from BackOfficeDatabaseSeeder.
 */
class LandlordContractFeesSeeder extends Seeder
{
    /** Columns this seeder owns, in report order. */
    const COLUMNS = [
        'management_method',
        'landlord_contract_management_fee',
        'landlord_contract_percentage',
        'landlord_contract_cleaning_charge',
        'landlord_contract_facility_management_fee',
        'landlord_contract_renewal_fee',
        'landlord_contract_new_leasing_fee_type',
        'landlord_contract_new_leasing_fee',
    ];

    const DEFAULT_PERCENTAGE_BASIS = 2; // 2 = Rent Collection (user decision 2026-09-14)

    public function run()
    {
        $apply = getenv('LC_FEES_APPLY') === '1';
        $rows  = require __DIR__ . '/data/landlord_contract_fees.php';

        $changes = [];   // one per contract with at least one differing column
        $warnings = [];
        $skipped = [];
        $buildingsTouched = [];

        foreach ($rows as $row) {
            $label = sprintf('#%d %s', $row['sno'], $row['sheet']);

            if ($row['code'] === null) {
                $skipped[] = [$label, 'no building mapped'];
                continue;
            }

            $building = Building::where('building_code', $row['code'])->first();
            if (!$building) {
                $skipped[] = [$label, 'building_code ' . $row['code'] . ' not found'];
                continue;
            }
            if ($building->building_name !== $row['db_name']) {
                $warnings[] = sprintf('%s: building_code %s is "%s" here, was "%s" when mapped', $label, $row['code'], $building->building_name, $row['db_name']);
            }
            if ($row['review']) {
                $warnings[] = sprintf('%s → "%s" was mapped by name search; double-check', $label, $building->building_name);
            }

            try {
                $parsed = LandlordContractFeeRuleParser::parse($row['fee'], $row['cleaning']);
            } catch (InvalidArgumentException $e) {
                $skipped[] = [$label, $e->getMessage()];
                continue;
            }

            $contracts = LandlordContract::active()->where('building_id', $building->id)->get();
            if ($contracts->isEmpty()) {
                $skipped[] = [$label, 'no approved landlord contract for "' . $building->building_name . '"'];
                continue;
            }
            if ($contracts->count() > 1) {
                $warnings[] = sprintf('%s: "%s" has %d approved contracts — all updated', $label, $building->building_name, $contracts->count());
            }

            foreach ($contracts as $contract) {
                $new = $parsed;
                if ($new['landlord_contract_cleaning_charge'] === null) {
                    unset($new['landlord_contract_cleaning_charge']); // blank cell → leave as is
                }
                // Percentage contracts need a valid basis (1 income / 2 collection) or the
                // tax-invoice calc skips the management fee; normalise junk to the default.
                if ($new['management_method'] === LandlordContractFeeRuleParser::METHOD_PERCENTAGE
                    && !in_array((int) $contract->landlord_contract_percentage, [1, 2], true)) {
                    $new['landlord_contract_percentage'] = self::DEFAULT_PERCENTAGE_BASIS;
                }

                $before = [];
                $after  = [];
                foreach ($new as $col => $val) {
                    if (!self::same($contract->$col, $val)) {
                        $before[$col] = $contract->$col;
                        $after[$col]  = $val;
                    }
                }
                if (!$after) {
                    continue;
                }
                $changes[] = [
                    'contract_id' => $contract->id,
                    'contract_no' => $contract->landlord_contract_no,
                    'building'    => $building->building_name,
                    'before'      => $before,
                    'after'       => $after,
                ];
                $buildingsTouched[$building->id] = true;
            }
        }

        $this->report($changes, $warnings, $skipped, count($buildingsTouched), $apply);

        if (!$apply || !$changes) {
            return;
        }

        $backup = storage_path('app/landlord_contract_fees_backup_' . date('Ymd_His') . '.json');
        file_put_contents($backup, json_encode($changes, JSON_PRETTY_PRINT));

        DB::transaction(function () use ($changes) {
            foreach ($changes as $c) {
                LandlordContract::where('id', $c['contract_id'])->update($c['after']);
            }
        });

        $this->command->info(sprintf('APPLIED %d contract(s). Backup of previous values: %s', count($changes), $backup));
    }

    private static function same($current, $new)
    {
        if ($current === null || $current === '') {
            return $new === null;
        }
        if ($new === null) {
            return false;
        }
        return abs((float) $current - (float) $new) < 0.0005;
    }

    private function report(array $changes, array $warnings, array $skipped, $buildings, $apply)
    {
        $cmd = $this->command;
        $tableRows = [];
        foreach ($changes as $c) {
            $first = true;
            foreach ($c['after'] as $col => $val) {
                $tableRows[] = [
                    $first ? $c['contract_no'] : '',
                    $first ? $c['building'] : '',
                    $col,
                    self::fmt($c['before'][$col]),
                    self::fmt($val),
                ];
                $first = false;
            }
        }
        if ($tableRows) {
            $cmd->table(['Contract', 'Building', 'Column', 'Before', 'After'], $tableRows);
        } else {
            $cmd->info('No changes — every mapped contract already matches the sheet.');
        }

        if ($warnings) {
            $cmd->warn('WARNINGS (' . count($warnings) . ')');
            foreach ($warnings as $w) {
                $cmd->line('  - ' . $w);
            }
        }
        if ($skipped) {
            $cmd->warn('SKIPPED (' . count($skipped) . ')');
            foreach ($skipped as $s) {
                $cmd->line('  - ' . $s[0] . ': ' . $s[1]);
            }
        }

        $cmd->info(sprintf('%d contract(s) across %d building(s) to change; %d sheet row(s) skipped.', count($changes), $buildings, count($skipped)));
        if (!$apply) {
            $cmd->warn('DRY RUN — nothing written. Re-run with LC_FEES_APPLY=1 to apply.');
        }
    }

    private static function fmt($v)
    {
        if ($v === null || $v === '') {
            return 'NULL';
        }
        return is_numeric($v) ? rtrim(rtrim(number_format((float) $v, 3, '.', ''), '0'), '.') : (string) $v;
    }
}
