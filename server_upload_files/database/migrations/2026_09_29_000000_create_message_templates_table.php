<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Editable email + SMS templates (system templates keyed for code, bulk templates for sending). */
class CreateMessageTemplatesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('message_templates')) {
            return;
        }
        Schema::create('message_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('key', 100)->nullable()->unique();
            $table->string('name', 150);
            $table->string('kind', 10)->default('bulk');
            $table->string('audience', 20)->default('any');
            $table->string('email_subject', 255)->nullable();
            $table->text('email_body')->nullable();
            $table->text('sms_body')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('message_templates');
    }
}
