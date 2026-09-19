<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

class OnboardingChecklistItem extends Model
{
    use \App\Concerns\LegacyDateSerialization;

    protected $guarded = [];
    protected $casts = ['required' => 'boolean', 'needs_photo' => 'boolean', 'is_active' => 'boolean', 'sort_order' => 'integer'];

    public const INPUT_TYPES = ['check', 'text', 'number', 'count', 'photo', 'meter'];

    public function scopeActive($q)
    {
        return $q->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }
}
