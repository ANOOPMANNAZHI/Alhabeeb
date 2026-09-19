<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

class TenantOnboardingPhoto extends Model
{
    use \App\Concerns\LegacyDateSerialization;

    protected $guarded = [];

    public function entry()
    {
        return $this->belongsTo(TenantOnboardingEntry::class, 'entry_id');
    }
}
