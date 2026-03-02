<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class DepositRefundDimension extends Model
{
    use Sortable;

    protected $table = 'deposit_refund_dim';    
    protected $guarded = [];   
    public $timestamps = false; 
    /*
    *
    *  AccountCodes
    */
    public function accountCode(){

      return $this->belongsTo('Modules\BackOffice\Entities\AccountCodes','ac_codes_id');
    }
    /**
     * Get all of the owning dim1 models.
     */
    public function dim1()
    {
        return $this->morphTo();
    }
   
     /**
     * Get all of the owning dim1 models.
     */
    public function dim2()
    {
        return $this->morphTo();
    }
}
