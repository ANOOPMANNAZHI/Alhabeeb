<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Customer receipt for a deposit refund that withheld money.
 * Deliberately separate from receipts_generation: it never posts to AX.
 */
class DepositRefundReceipt extends Model
{
    protected $table = 'deposit_refund_receipt';
    protected $guarded = [];
    protected $dates = ['receipt_date'];

    public function depositRefund()
    {
        return $this->belongsTo(DepositRefund::class, 'deposit_refund_id');
    }

    public function lines()
    {
        return $this->hasMany(DepositRefundReceiptLine::class, 'deposit_refund_receipt_id')
                    ->orderBy('line_order');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }

    public function deductionLines()
    {
        return $this->lines()->where('line_type', 'deduction');
    }
}
