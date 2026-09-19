<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_devices', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->unique();     // one device per user (spec: first login binds)
            $t->string('device_uid', 191)->index();
            $t->string('device_name')->nullable();
            $t->string('platform', 20)->nullable();
            $t->string('app_version', 20)->nullable();
            $t->timestamp('registered_at');
            $t->timestamp('last_seen_at')->nullable();
            $t->timestamp('blocked_at')->nullable();
            $t->unsignedBigInteger('blocked_by')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_devices');
    }
};
