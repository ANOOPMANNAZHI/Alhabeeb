<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;
 

class LandlordInvoiceDimension extends Model
{ 
	protected $guarded = [];

	 public $timestamps = false; 

	 /**
     * Get all of the owning dim1able models.
     */
    public function dim1able()
    {
        return $this->morphTo();
    }
   
     /**
     * Get all of the owning dim1able models.
     */
    public function dim2able()
    {
        return $this->morphTo();
    }


    /*
    *
    *  AccountCodes
    */
    public function accountCode(){

      return $this->belongsTo('Modules\BackOffice\Entities\AccountCodes','ac_codes_id');
    } 
  
}
