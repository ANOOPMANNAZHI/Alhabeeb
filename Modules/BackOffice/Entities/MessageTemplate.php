<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MessageTemplate extends Model
{
    use \App\Concerns\LegacyDateSerialization;
    use SoftDeletes;

    const KIND_SYSTEM = 'system';
    const KIND_BULK = 'bulk';

    protected $table = 'message_templates';
    protected $guarded = [];
    protected $casts = ['is_active' => 'boolean'];

    public function isSystem()
    {
        return $this->kind === self::KIND_SYSTEM;
    }

    /** Active system template by key, or null (callers fall back to their built-in wording). */
    public static function system($key)
    {
        return static::where('key', $key)->where('kind', self::KIND_SYSTEM)->where('is_active', true)->first();
    }
}
