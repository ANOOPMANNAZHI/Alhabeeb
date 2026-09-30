<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Per-template SMS switch, and one log row per email/SMS attempt (project C). */
class AddSmsEnabledAndMessageLog extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('message_templates', 'sms_enabled')) {
            Schema::table('message_templates', function (Blueprint $table) {
                $table->boolean('sms_enabled')->default(false);
            });
        }
        if (!Schema::hasTable('message_log')) {
            Schema::create('message_log', function (Blueprint $table) {
                $table->increments('id');
                $table->string('template_key', 100)->nullable();
                $table->string('channel', 10);
                $table->string('recipient_name', 150)->nullable();
                $table->string('recipient', 190)->nullable();
                $table->text('cc')->nullable();
                $table->string('subject', 255)->nullable();
                $table->text('body')->nullable();
                $table->string('status', 30);
                $table->text('note')->nullable();
                $table->string('related_type', 50)->nullable();
                $table->integer('related_id')->nullable();
                $table->integer('triggered_by')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->index('created_at');
                $table->index('template_key');
                $table->index(['related_type', 'related_id']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('message_log');
        if (Schema::hasColumn('message_templates', 'sms_enabled')) {
            Schema::table('message_templates', function (Blueprint $table) {
                $table->dropColumn('sms_enabled');
            });
        }
    }
}
