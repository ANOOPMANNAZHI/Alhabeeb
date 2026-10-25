<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Field-level change history of inspection issues: every action plan edit (and the priority /
 * target date changes a "Worsened" follow-up makes) - which field, from what, to what, who, when.
 */
class CreateInspectionIssueChanges extends Migration
{
    public function up()
    {
        Schema::create('inspection_issue_changes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('issue_id');
            $table->integer('update_id')->nullable(); // the timeline entry the change belongs to
            $table->string('field', 40);
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->integer('changed_by');
            $table->timestamp('changed_at');
            $table->index(['issue_id', 'changed_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('inspection_issue_changes');
    }
}
