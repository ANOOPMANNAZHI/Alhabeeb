<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/** Bulk send (Mail -> Send): one row per send and one per recipient; texts and recipients are frozen at start. */
class CreateBulkSendsTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('bulk_sends')) {
            Schema::create('bulk_sends', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('template_id')->nullable();
                $table->string('audience', 20);
                $table->text('filters')->nullable();
                $table->boolean('send_email')->default(true);
                $table->boolean('send_sms')->default(false);
                $table->string('email_subject', 255)->nullable();
                $table->text('email_body')->nullable();
                $table->text('sms_body')->nullable();
                $table->string('status', 20)->default('running');
                $table->string('note', 255)->nullable();
                $table->unsignedInteger('total')->default(0);
                $table->unsignedInteger('sent')->default(0);
                $table->unsignedInteger('failed')->default(0);
                $table->unsignedInteger('skipped')->default(0);
                $table->unsignedInteger('email_fail_streak')->default(0);
                $table->unsignedInteger('created_by')->nullable();
                $table->timestamps();
                $table->timestamp('finished_at')->nullable();
                $table->index('status');
            });
        }
        if (!Schema::hasTable('bulk_send_recipients')) {
            Schema::create('bulk_send_recipients', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('bulk_send_id');
                $table->string('person_type', 10);
                $table->unsignedInteger('person_id');
                $table->string('name', 150)->nullable();
                $table->string('email', 190)->nullable();
                $table->string('mobile', 30)->nullable();
                $table->text('values')->nullable();
                $table->string('status', 12)->default('pending');
                $table->string('note', 255)->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->index(['bulk_send_id', 'status']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('bulk_send_recipients');
        Schema::dropIfExists('bulk_sends');
    }
}
