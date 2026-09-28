<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Per-contract lookups on large tables had no index, so every one read the whole
 * table (receipts_generation: ~256k rows, ~0.4 s per query). Termination dues,
 * rent outstanding, the receipt screens and termination screens all filter by
 * contract. IF NOT EXISTS: safe to re-run and harmless if an index was added by hand.
 */
class AddContractLookupIndexes extends Migration
{
    private $indexes = [
        'receipts_generation_tenant_contract_id_idx'  => 'receipts_generation (tenant_contract_id)',
        'receipts_generation_dim_receipt_id_idx'      => 'receipts_generation_dim (receipts_generation_id)',
        'pdc_tenant_contract_id_idx'                  => 'pdc (tenant_contract_id)',
        'termination_contract_id_idx'                 => 'termination (contract_id)',
    ];

    public function up()
    {
        foreach ($this->indexes as $name => $on) {
            DB::statement('CREATE INDEX IF NOT EXISTS ' . $name . ' ON ' . $on);
        }
    }

    public function down()
    {
        foreach (array_keys($this->indexes) as $name) {
            DB::statement('DROP INDEX IF EXISTS ' . $name);
        }
    }
}
