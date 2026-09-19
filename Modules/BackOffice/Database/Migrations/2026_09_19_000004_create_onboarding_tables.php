<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant onboarding (move-in) checklist: an admin-configurable master list of
 * items, one onboarding per active contract, one entry per item with photos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onboarding_checklist_items', function (Blueprint $t) {
            $t->id();
            $t->string('group_name', 100);
            $t->string('title', 200);
            $t->string('input_type', 20)->default('check'); // check|text|number|count|photo|meter
            $t->boolean('required')->default(true);
            $t->boolean('needs_photo')->default(false);
            $t->unsignedInteger('sort_order')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
        Schema::create('tenant_onboardings', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('tenant_contract_id')->unique();
            $t->unsignedBigInteger('unit_id')->nullable();
            $t->unsignedBigInteger('building_id')->nullable()->index();
            $t->unsignedBigInteger('assigned_user_id')->nullable()->index();
            $t->string('status', 20)->default('open'); // open|completed|verified|cancelled
            $t->timestamp('started_at')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->unsignedBigInteger('verified_by')->nullable();
            $t->timestamp('verified_at')->nullable();
            $t->string('tenant_signature')->nullable();
            $t->text('tenant_remark')->nullable();
            $t->string('pdf_path')->nullable();
            $t->timestamps();
        });
        Schema::create('tenant_onboarding_entries', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('tenant_onboarding_id')->index();
            $t->unsignedBigInteger('item_id');
            $t->text('value')->nullable();
            $t->text('remark')->nullable();
            $t->boolean('done')->default(false);
            $t->boolean('not_applicable')->default(false);
            $t->timestamps();
            $t->unique(['tenant_onboarding_id', 'item_id']);
        });
        Schema::create('tenant_onboarding_photos', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('entry_id')->index();
            $t->string('path');
            $t->string('thumbnail')->nullable();
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_onboarding_photos');
        Schema::dropIfExists('tenant_onboarding_entries');
        Schema::dropIfExists('tenant_onboardings');
        Schema::dropIfExists('onboarding_checklist_items');
    }
};
