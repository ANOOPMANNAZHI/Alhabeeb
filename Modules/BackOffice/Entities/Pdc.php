<?php

namespace Modules\BackOffice\Entities;
use DB;
use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
class Pdc extends Model
{
	use Sortable;

    const TYPE_RENT    = 1;
    const TYPE_DEPOSIT = 2;
    const TYPE_OTHERS  = 3;
    const TYPE_VAT     = 4;
    protected $fillable = [];
    protected $guarded = [];
    protected $table = 'pdc';
    protected $dates = ['pdc_transaction_date','pdc_check_date','pdc_recieve_date','pdc_deposit_date','pdc_clear_date','pdc_cancel_date','pdc_posted_date','created_at'];
	protected $casts = [
        'pdc_check_no' => 'integer',
    ];
	
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
          case '3' : return 'Others';
          case '4' : return 'VAT';
        }
      }
      
    /*
    * PDC Cance Reason
    */
     public function getPdcCancelReasonNameAttribute()
      {
        // names come from Masters -> Bounce Reasons (switched-off reasons still show their name)
        return \Modules\Masters\Entities\PdcBounceReason::nameFor($this->pdc_bounce_reason);
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

/**
 * SQL condition, true when contract $tc (tenant $t) is the tenant's first contract: no earlier
 * contract for the tenant, nor for another tenant record with the same resident ID or mobile
 * (duplicate tenant records). Renewals always have an earlier contract, so they are never first.
 */
public static function firstContractSql($tc = 'tc', $t = 't'){
    // three separate checks instead of one OR, so Postgres can hash them (the OR scanned every
    // contract per row); a blank resident ID / mobile is null after nullif and never matches
    $earlier = "o.id <> {$tc}.id and o.tenant_contract_effective_date < {$tc}.tenant_contract_effective_date";
    return "not exists (select 1 from tenant_contracts o where o.tenant_id = {$tc}.tenant_id and {$earlier})
        and not exists (select 1 from tenant_contracts o join tenant t2 on t2.id = o.tenant_id
          where trim(t2.resident_id) = nullif(trim({$t}.resident_id), '') and {$earlier})
        and not exists (select 1 from tenant_contracts o join tenant t2 on t2.id = o.tenant_id
          where trim(t2.tenant_contact_no) = nullif(trim({$t}.tenant_contact_no), '') and {$earlier})";
}

/** Cheques of a contract that is the tenant's first contract (a new tenant). */
public function scopeOnNewTenantContract($query){
    return $query->whereExists(function ($q) {
        $q->select(DB::raw(1))->from('tenant_contracts as nt_tc')
          ->join('tenant as nt_t', 'nt_t.id', '=', 'nt_tc.tenant_id')
          ->whereColumn('nt_tc.id', 'pdc.tenant_contract_id')
          ->whereRaw(self::firstContractSql('nt_tc', 'nt_t'));
    });
}

/** Cheques of contracts whose sales enquiry was handled by the given user (the sales person). */
public function scopeSoldBy($query, $userId){
    return $query->whereExists(function ($q) use ($userId) {
        $q->select(DB::raw(1))->from('tenant_contracts as sb_tc')
          ->join('sales_enquiries as sb_se', 'sb_se.id', '=', 'sb_tc.sale_enquiry_id')
          ->whereColumn('sb_tc.id', 'pdc.tenant_contract_id')
          ->where('sb_se.assigned_person', $userId);
    });
}

/**
 * Bounces still to be followed up (ARE / back office manager dashboard card and list):
 * bounced, contract active, not settled (pdc_settlement 2), no bounce receipt that is not
 * cancelled/rejected, and not deposited again (later open PDC with the same cheque no, bank,
 * date and amount - same rules as PdcController::bounceReceiptNos / redepositedIds).
 */
public function scopeOpenBounces($query){
    return $query->where('pdc.pdc_cancel_reason', 1)
        ->where(function ($q) {
            $q->whereNull('pdc.pdc_settlement')->orWhere('pdc.pdc_settlement', '<>', 2);
        })
        ->whereHas('tenantContractInfo', function ($q) {
            $q->where('tenant_contract_status', 1);
        })
        ->whereNotExists(function ($q) {
            $q->select(\DB::raw(1))->from('receipts_generation as rg')
              ->whereColumn('rg.receipts_generation_is_pdc_bounce_id', 'pdc.id')
              ->where('rg.receipts_generation_status', '!=', 2)
              ->where('rg.receipts_generation_approval_status', '!=', 4)
              ->whereNull('rg.deleted_at');
        })
        ->whereNotExists(function ($q) {
            $q->select(\DB::raw(1))->from('pdc as rd')
              ->whereColumn('rd.tenant_contract_id', 'pdc.tenant_contract_id')
              ->whereColumn('rd.id', '>', 'pdc.id')
              ->where('rd.pdc_cancel_reason', 0)
              ->whereRaw("ltrim(rd.pdc_check_no::text, '0') = ltrim(pdc.pdc_check_no::text, '0')")
              ->whereRaw('coalesce(rd.bank_id, 0) = coalesce(pdc.bank_id, 0)')
              ->whereRaw('rd.pdc_check_date::date = pdc.pdc_check_date::date')
              ->whereRaw('round(rd.pdc_amt::numeric, 3) = round(pdc.pdc_amt::numeric, 3)');
        });
}

/*
 * One physical cheque can be entered as several PDC lines (rent + Others / VAT on the same cheque).
 * Cards count cheques, not lines: same contract + cheque no (leading zeros ignored) + cheque date.
 * A line without a cheque no stays on its own. chequeKey() is the same rule for a loaded row.
 */
public static function countCheques($query){
    return $query->count(DB::raw("distinct (pdc.tenant_contract_id, coalesce(nullif(ltrim(pdc.pdc_check_no::text, '0'), ''), 'line ' || pdc.id), pdc.pdc_check_date::date)"));
}

public static function chequeKey($p){
    $no = ltrim(trim((string) $p->pdc_check_no), '0');
    return $p->tenant_contract_id . '|' . ($no !== '' ? $no : 'line ' . $p->id) . '|' . substr((string) $p->pdc_check_date, 0, 10);
}

}
