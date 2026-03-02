<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

class GeneralLedgerDim extends Model
{    
    protected $guarded = [];
    protected $table = 'general_ledger_dim';

    public $timestamps = false; 
    
    /*
    *
    *  AccountCodes
    */
    public function accountCode(){

      return $this->belongsTo('Modules\BackOffice\Entities\AccountCodes','account_id');
    } 
    /*
    *
    *  Building
    */
    public function building(){

      return $this->belongsTo('Modules\Masters\Entities\Building');
    } 
    /*
    *
    *  Unit
    */
    public function unit(){

      return $this->belongsTo('Modules\Masters\Entities\Unit');
    }
    
	  /*
    *
    *  Unit by Building
    */
    public function unitByBuilding(){

      return $this->hasMany('Modules\Masters\Entities\Unit','building_id','building_id');
    }
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
   *  recovery
   *
   */
   public function getRecoveryValAttribute(){

   	  switch($this->recovery){

   	  	case 0 :  $recovery =  'No';
   	  			  break;	
   	  	case 1 :  $recovery =  'Yes';
   	  	          break;	  

   	  }
   	  return $recovery;
   }
    
}
