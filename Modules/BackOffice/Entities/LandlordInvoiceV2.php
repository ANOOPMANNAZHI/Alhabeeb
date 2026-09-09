<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use Modules\Masters\Entities\Vendor;
use Modules\Sales\Entities\LandlordContract;

class LandlordInvoiceV2 extends Model
{
    use Sortable;

    protected $table = 'landlord_invoice_v2';
    protected $guarded = [];
    public $sortable = ['invoice_no', 'invoice_date', 'vendor_name', 'building_name', 'grand_total', 'status'];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function landlordContract()
    {
        return $this->belongsTo(LandlordContract::class, 'landlord_contract_id');
    }

    public function lines()
    {
        return $this->hasMany(LandlordInvoiceV2Line::class, 'landlord_invoice_v2_id')->orderBy('line_order');
    }

    public function postedBy()
    {
        return $this->belongsTo(\App\User::class, 'posted_by');
    }

    public function isPosted()
    {
        return $this->status === 'posted';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getInvoiceTypeLabelAttribute()
    {
        return $this->invoice_type === 'tax_invoice' ? 'Tax Invoice' : 'Other Deductions';
    }
}
