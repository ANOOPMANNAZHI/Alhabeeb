<?php

namespace Modules\BackOffice\Entities;
use DB;
use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
class Pdc extends Model
{
    use \App\Concerns\LegacyDateSerialization;
	use Sortable;
    protected $fillable = [];
    protected $guarded = [];
    protected $table = 'pdc';
	protected $casts = ['pdc_check_no' => 'integer', 'pdc_transaction_date' => 'datetime', 'pdc_check_date' => 'datetime', 'pdc_recieve_date' => 'datetime', 'pdc_deposit_date' => 'datetime', 'pdc_clear_date' => 'datetime', 'pdc_cancel_date' => 'datetime', 'pdc_posted_date' => 'datetime', 'created_at' => 'datetime'];
	
    public static function paymentTermInfo($payment_term){
 
    	switch($payment_term){

                    case 1:
                        // Monthly
                        $month = 1;
                        break;

                    case 2:
                        // Bi-Monthly
                        $month = 2;
                        break;

                    case 3:
                        // Quaterly Monthly
                        $month = 3;
                        break;

                    case 4:
                        // Half Yealy 
                        $month = 6;
                        break;
                    case 5:
                         // Yealy 
                        $month = 12;
                        break;
                    
                    default :
                        
                        break;
        }

        return $month;

    }
   
    /*
    * Tenant Contract Info
    */
    public function tenantContractInfo(){

      return $this->belongsTo('Modules\Sales\Entities\TenantContract','tenant_contract_id','id');
    }
    /*
    * Bank Info
    */
    public function bankInfo(){

      return $this->belongsTo('Modules\Masters\Entities\Bank','bank_id','id');
    }
    /*
    * PDC Type
    */
     public function getPdcTypeNameAttribute()
      {     
        switch($this->pdc_type){
          case '1' : return 'Rent';        
          case '2' : return 'Deposit';
        }
      }
      
    /*
    * PDC Cance Reason
    */
     public function getPdcCancelReasonNameAttribute()
      {     
        switch($this->pdc_bounce_reason){
          case '1' : return 'Insufficient Funds';        
          case '2' : return 'Signature Missing';
          case '3' : return 'Signature Mismatch';
          case '4' : return 'Word in amount and figure differ';
          case '5' : return 'Stop Payment';
          case '6' : return 'Refer to Drawer';
          case '7' : return 'Correction';
          case '8' : return 'Stale Cheque (Beyond six months)';
          case '9' : return 'Misc';
        }
      }  
        /*
  *  Scope are
  *
  */
  public function scopeAreFilter($query){

    $user = \Auth::user();
    $role_count = count($user->roles);
    $headUser = \Auth::user()->id;

    if($role_count == 1 && $user->hasRole('are')){ 
       $query->whereHas('tenantContractInfo', function ($query){       
       $query->whereHas('building', function ($query){       
           $query->whereHas('areBuildings', function ($query){
             $query->where('user_id','=', \Auth::user()->id);
             return $query;
         });   
           return $query;   
       }); 
       }); 
   }
   elseif($role_count == 1 && $user->hasRole('are_team_lead')){
      $query->whereHas('tenantContractInfo', function ($query)use($headUser){
      $query->whereHas('building', function ($query)use($headUser){
          $query->whereHas('areBuildings', function ($query)use($headUser){
            $query->whereHas('user', function ($query)use($headUser){
                $query->whereHas('employee', function ($query)use($headUser){
                  $query->where('head_user','=', $headUser);
              });
            });
            $query->orWhere('user_id','=', \Auth::user()->id);
            return $query;
        });
      });
      });

  }

  return $query;
}
      /*
*
*Bounce Cheque
*
*/
public function scopeBouncedCheques($query){    
    $query->where(function ($query){
       $query->where('pdc_cancel_reason',1);
       
   });
    $query->orWhere('pdc_cancel_reason',1)->where('pdc_settlement',null);
    return $query;
}

}
