<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class Vendor extends Model
{
	use Sortable;
	
	protected $guarded = [];   
	public $sortable = ['id','vendor_name','vendor_code','vendor_status'];
    /*
    *
    * vendor table/contractor
    */
    public function contractor(){

      return $this->hasOne('Modules\Masters\Entities\ContractorWorkLink','vendor_id');
  	}   

  	/*
  	* Vendor Type
  	*
  	*/
  	  public function vendorType(){

      return $this->belongsTo('Modules\Masters\Entities\VendorType','vendor_type_id');
  	}
 
    /*
    * Status Name
    *
    */

    public function getVendorStatusNameAttribute()
    {     
        switch($this->vendor_status){
          case '1' : return 'Active';
          case '0' : return 'Inactive';        
        }
    }


    /*
    * Vendor Location
    *
    */
      public function vendorLocation(){

      return $this->belongsTo('Modules\Masters\Entities\Location','location_id');
    }



    /*
    * Vendor Bank
    *
    */
      public function vendorBank(){

      return $this->belongsTo('Modules\Masters\Entities\Bank','bank_id');
    }



    /*
    *  Status - Active
    *
    */
    public function scopeActive($query)
    {
        return $query->where('vendor_status', 1);
    }  
    
    
    
    
    
  
  public function vendorStatusSortable($query, $direction)
		{
			$direction = ($direction == 'asc')? 'desc' : 'asc';
			return $query->orderBy('vendor_status', $direction);
		}
/*
    *
    * Landlord Contract
    */
    public function landlordContract(){

      return $this->hasMany('Modules\Sales\Entities\LandlordContract','vendor_id','id')->where('landlord_contract_status',1);
    }
 


}
