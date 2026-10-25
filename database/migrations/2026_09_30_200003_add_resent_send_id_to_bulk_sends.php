<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/** Resend to failed may run only once per send: the new send's id is kept on the old one. */
class AddResentSendIdToBulkSends extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('bulk_sends', 'resent_send_id')) {
            Schema::table('bulk_sends', function (Blueprint $table) {
                $table->unsignedBigInteger('resent_send_id')->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('bulk_sends', 'resent_send_id')) {
            Schema::table('bulk_sends', function (Blueprint $table) {
                $table->dropColumn('resent_send_id');
            });
        }
    }
}
