<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

class TenantOnboarding extends Model
{
    use \App\Concerns\LegacyDateSerialization;

    protected $guarded = [];
    protected $casts = ['started_at' => 'datetime', 'completed_at' => 'datetime', 'verified_at' => 'datetime'];

    public function tenantContract()
    {
        return $this->belongsTo(\Modules\Sales\Entities\TenantContract::class, 'tenant_contract_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(\App\User::class, 'assigned_user_id');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(\App\User::class, 'verified_by');
    }

    public function entries()
    {
        return $this->hasMany(TenantOnboardingEntry::class, 'tenant_onboarding_id');
    }
}
