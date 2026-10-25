<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The open-bounces rule (Bounced Cheques cards and list) checks "no bounce receipt yet" by
 * receipts_generation_is_pdc_bounce_id, which had no index, so every check read the whole
 * receipts table (~260k rows). IF NOT EXISTS: safe to re-run. Adds an index only, no data change.
 */
class AddReceiptsBouncePdcIndex extends Migration
{
    public function up()
    {
        DB::statement('CREATE INDEX IF NOT EXISTS receipts_generation_is_pdc_bounce_id_idx ON receipts_generation (receipts_generation_is_pdc_bounce_id)');
    }

    public function down()
    {
        DB::statement('DROP INDEX IF EXISTS receipts_generation_is_pdc_bounce_id_idx');
    }
}
