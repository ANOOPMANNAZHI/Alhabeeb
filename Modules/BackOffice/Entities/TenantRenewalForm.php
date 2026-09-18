<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

class TenantRenewalForm extends Model
{
    use \App\Concerns\LegacyDateSerialization;
    protected $guarded = [];
    protected $table = 'tenant_renewal_forms';
    protected $casts = ['tenant_contract_start_date' => 'datetime', 'tenant_contract_valid_to_date' => 'datetime'];

    /*
    *
    *  Tenant payment Method
    */
    public function getTenantContractPaymentNameAttribute()
    {     
        switch($this->tenant_contract_payment_type){
          case '1' : return 'Monthly';
          case '2' : return 'Bi-Monthly'; 
          case '3' : return 'Quarterly';  
          case '4' : return 'Half Yearly'; 
          case '5' : return 'Yearly';       
        }
    }
}
