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

    /** Expense head (acc_codes row) picked on manually added lines; null on calculated lines. */
    public function accountCode()
    {
        return $this->belongsTo(AccountCodes::class, 'acc_codes_id');
    }

    /** "12306 - MUNICIPAL TAX" or null when the line has no head. */
    public function getHeadLabelAttribute()
    {
        $ac = $this->accountCode;
        return $ac ? trim($ac->acc_code_val . ' - ' . $ac->acc_code_desc) : null;
    }
}
