<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

class DepositRefundReceiptLine extends Model
{
    protected $table = 'deposit_refund_receipt_line';
    protected $guarded = [];

    public function receipt()
    {
        return $this->belongsTo(DepositRefundReceipt::class, 'deposit_refund_receipt_id');
    }
}
