<?php

namespace Modules\Sales\Entities;

use Illuminate\Database\Eloquent\Model;

class TenantDocument extends Model
{
    use \App\Concerns\LegacyDateSerialization;
    
    protected $guarded = [] ;
    protected $table = 'tenant_documents';
}
