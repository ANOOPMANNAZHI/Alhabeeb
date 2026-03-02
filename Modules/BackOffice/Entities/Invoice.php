<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [];
    protected $guarded = [];
    protected $table = 'invoice';
    protected $dates = ['tenant_invoice_date','tenant_invoice_posted_date','tenant_invoice_cancelled_date','created_at'];


    /*
    * Tenant Contract Building
    */
    public function tenantContractInfo(){

      return $this->belongsTo('Modules\Sales\Entities\TenantContract','tenant_contract_id','id');
    }

    /*
    * Invoice Dimenision details
    */
    public function tenantInvoiceDimensionInfo(){
    
      return $this->hasMany('Modules\BackOffice\Entities\TenantInvoiceDimension','invoice_id');
    }

    

}
