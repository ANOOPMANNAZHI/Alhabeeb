<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Columns spatie/laravel-activitylog v5 expects that the v3-era table
 * does not have. `properties` stays text: Postgres cannot cast the
 * existing rows to json implicitly and the model casts it anyway.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            if (!Schema::hasColumn('activity_log', 'event')) {
                $table->string('event')->nullable()->after('subject_type');
            }
            if (!Schema::hasColumn('activity_log', 'attribute_changes')) {
                $table->json('attribute_changes')->nullable()->after('causer_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropColumn(['event', 'attribute_changes']);
        });
    }
};
