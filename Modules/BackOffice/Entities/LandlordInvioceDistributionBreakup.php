<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;
 

class LandlordInvioceDistributionBreakup extends Model
{ 
	protected $guarded = [];

	protected $table = 'landlord_invioce_distribution_break_up';
	
	public $timestamps = false; 
	
	/*
    * Landlord Contract 
    */
    public function landlordContractInfo(){

    	return $this->belongsTo('Modules\Sales\Entities\LandlordContract','landlord_contract_id','id');
    }
   /*
    *
    *Landlord Invoice Dimension
    *
    *
    */
    public function landlordInvoiceDimension(){

      return $this->hasMany('Modules\BackOffice\Entities\LandlordInvoiceDimension','landlord_invoice_id');
    }
    
   /*
    *
    *Landlord Invoice Dimension
    *
    *
    */
    public function landlordInvoice(){

      return $this->belongsTo('Modules\BackOffice\Entities\LandlordInvoice','landlord_invoice_id','id');
    }
  
}
