<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

class TenantInvoiceDimension extends Model
{
    use \App\Concerns\LegacyDateSerialization;
    protected $guarded = [];
    protected $table = 'tenant_invoice_dimensions';
    protected $casts = ['created_at' => 'datetime'];


    

}
