<?php

namespace Modules\Maintenance\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class MaintenanceInvoiceDetails extends Model
{
	use Sortable;

    protected $table = 'maintenance_invoice_details';    
    protected $guarded = [];   

    public $timestamps = false; 

    
    /*
    *
    *  AccountCodes
    */
    public function accountCode(){

      return $this->belongsTo('Modules\BackOffice\Entities\AccountCodes','ac_codes_id');
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
    *  Unit
    */
    public function technician(){

      return $this->belongsTo('App\User')->with('employee');
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
    * Technician Recovery 
    *
    */
    public function getTechnicianRecoveryStatusAttribute()
    {
       if($this->technician_recovery == 1)
       	return 'Yes';
       else
       	return 'No';
    }
    
    
    /*
    *
    *  Expense Head
    */
    public function ExpenseHead(){

      return $this->belongsTo('Modules\BackOffice\Entities\ExpenseHead','ac_codes_id');
    }
    



}
