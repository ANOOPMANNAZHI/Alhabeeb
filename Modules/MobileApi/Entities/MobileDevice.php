<?php

namespace Modules\MobileApi\Entities;

use Illuminate\Database\Eloquent\Model;

class MobileDevice extends Model
{
    use \App\Concerns\LegacyDateSerialization;

    protected $guarded = [];
    protected $casts = ['registered_at' => 'datetime', 'last_seen_at' => 'datetime', 'blocked_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }

    public function isBlocked(): bool
    {
        return $this->blocked_at !== null;
    }
}
