<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

class TenantInvoiceDimension extends Model
{
    protected $guarded = [];
    protected $table = 'tenant_invoice_dimensions';
    protected $dates = ['created_at'];


    

}
