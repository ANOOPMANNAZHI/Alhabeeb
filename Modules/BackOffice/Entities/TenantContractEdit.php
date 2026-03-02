<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class TenantContractEdit extends Model
{
	use Sortable;
    protected $guarded = [];
    protected $fillable = [];
    protected $table = 'tenant_contracts';
  
    protected $dates = ['tenant_contract_start_date','tenant_contract_effective_date','tenant_contract_valid_to_date','tenant_contract_valid_from_date','created_at','tenant_contract_last_paid_date','tenant_contract_receipt_date'];
    public $sortable = ['tenant_contract_no'];
    /*
  	*
  	* Building
  	**/
  	public function building(){

  		 return $this->belongsTo('Modules\Masters\Entities\Building','building_id');
  	}
  	/*
  	*
  	* Unit
  	**/
  	public function unit(){

  		 return $this->belongsTo('Modules\Masters\Entities\Unit','unit_id');
  	}
  	/*
  	*
  	* Tenant
  	**/
  	public function tenant(){

  		 return $this->belongsTo('Modules\Sales\Entities\Tenant','tenant_id');
  		 

  	}
    /*
    * Status Name
    *
    */

    public function gettenantContractStatusNameAttribute()
    {     
        switch($this->tenant_contract_status){
          case '1' : return 'Active';
          case '0' : return 'Inactive';        
        }
    }
}
