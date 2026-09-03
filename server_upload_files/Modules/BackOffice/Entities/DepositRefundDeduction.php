<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

class DepositRefundDeduction extends Model
{
    protected $table = 'deposit_refund_deduction';
    protected $guarded = [];

    public function depositRefund()
    {
        return $this->belongsTo('Modules\BackOffice\Entities\DepositRefund', 'deposit_refund_id');
    }
}
