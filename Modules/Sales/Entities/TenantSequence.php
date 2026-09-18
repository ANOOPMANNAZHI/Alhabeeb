<?php

namespace Modules\Sales\Entities;

use Illuminate\Database\Eloquent\Model;

class TenantSequence extends Model
{
    use \App\Concerns\LegacyDateSerialization;
    protected $guarded = [] ;
    protected $table = 'tenant_sequence';
}
