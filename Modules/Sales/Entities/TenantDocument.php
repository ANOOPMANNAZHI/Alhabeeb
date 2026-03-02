<?php

namespace Modules\Sales\Entities;

use Illuminate\Database\Eloquent\Model;

class TenantDocument extends Model
{
    
    protected $guarded = [] ;
    protected $table = 'tenant_documents';
}
