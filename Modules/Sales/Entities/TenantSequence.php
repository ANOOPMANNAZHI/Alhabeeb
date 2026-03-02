<?php

namespace Modules\Sales\Entities;

use Illuminate\Database\Eloquent\Model;

class TenantSequence extends Model
{
    protected $guarded = [] ;
    protected $table = 'tenant_sequence';
}
