<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

class RenewalNote extends Model
{
    use \App\Concerns\LegacyDateSerialization;
    protected $guarded = [];
    protected $casts = ['created_at' => 'datetime'];
    /*
    *
    * Renewal
    */
    public function renewal(){

      return $this->belongsTo('Modules\BackOffice\Entities\Renewal');
  	}
  	/*
    * 
    *  CreatedBy
    *
    */
    public function createdBy() {
        
           return $this->belongsTo('App\User','created_by');
    }
/*
    * 
    *  UpdatedBy
    *
    */
    public function updatedByUser() {
        
           return $this->belongsTo('App\User','updated_by');
    }



    /*
    *
    * Renewal  Type 
    */
    public function renewalType(){

      return $this->belongsTo('Modules\BackOffice\Entities\ContractRenewalType','contract_renewal_type_id');
    }
}
