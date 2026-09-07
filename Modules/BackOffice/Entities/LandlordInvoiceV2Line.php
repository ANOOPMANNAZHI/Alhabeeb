<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

class LandlordInvoiceV2Line extends Model
{
    protected $table = 'landlord_invoice_v2_lines';
    protected $guarded = [];

    public function invoice()
    {
        return $this->belongsTo(LandlordInvoiceV2::class, 'landlord_invoice_v2_id');
    }
}
