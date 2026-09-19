<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

class TenantOnboardingEntry extends Model
{
    use \App\Concerns\LegacyDateSerialization;

    protected $guarded = [];
    protected $casts = ['done' => 'boolean', 'not_applicable' => 'boolean'];

    public function onboarding()
    {
        return $this->belongsTo(TenantOnboarding::class, 'tenant_onboarding_id');
    }

    public function item()
    {
        return $this->belongsTo(OnboardingChecklistItem::class, 'item_id');
    }

    public function photos()
    {
        return $this->hasMany(TenantOnboardingPhoto::class, 'entry_id');
    }
}
