<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

class AccountParams extends Model
{
    protected $fillable = [];
    
    protected $guarded = [];
    protected $table = 'acc_params';
    
    
     /*
    * Account Debit Code Details
    */
    public function tenantAccountCodeDebitInfo(){
  
      return $this->belongsTo('Modules\BackOffice\Entities\AccountCodes','acc_params_dr_acc','acc_code_val');
    }
     /*
    * Account Credit Code Details
    */
    public function tenantAccountCodeCreditInfo(){
    
      return $this->belongsTo('Modules\BackOffice\Entities\AccountCodes','acc_code_val','acc_params_cr_acc');
    }

   /*
    * Account Debit Code Details
    */
    public function debitAccountInfo(){
  
      return $this->belongsTo('Modules\BackOffice\Entities\AccountCodes','acc_params_dr_acc','acc_code_val');
    }
     /*
    * Account Credit Code Details
    */
    public function creditAccountInfo(){
    
      return $this->belongsTo('Modules\BackOffice\Entities\AccountCodes','acc_params_cr_acc','acc_code_val');
    }


}
