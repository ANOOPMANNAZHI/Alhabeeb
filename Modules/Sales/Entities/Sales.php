<?php

namespace Modules\Sales\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class Sales extends Model
{
	
	use Sortable;
    protected $guarded = [] ;
   
    protected $table = 'sales';
    
    public $sortable = ['id','sales_enquiry_id'];

    /*
    *
    * Sales Enquiry
    */
    public function salesEnquiry(){

      return $this->belongsTo('Modules\Sales\Entities\SalesEnquiry');
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
    * Work Flow Process
    */
    public function workFlowProcess(){

      return $this->belongsTo('Modules\General\Entities\WorkFlowProcess','work_flow_processes_code','work_flow_processes_code');
    }



    /*
    *  Sales User
    */
    public function salesUsers() {
           return $this->belongsToMany('\Spatie\Permission\Models\Role', 'sales_users','sales_id','role_id');
    }
    /*
    * 
    *  salesuser
    *
    */
    public function salesUser() {
        
           return $this->hasMany('Modules\Sales\Entities\SalesUsers','sales_id');
    }
    
	/*
    * 
    *  Landord sale enquiry
    *
    */
    public function landlordSale() {
        
           return $this->hasMany('Modules\Sales\Entities\LandordContract','sale_enquiry_id');
    }
    
    
    
    
    /*
    *
    * Sales Activity
    */
    public function salesActivity(){

      return $this->hasMany('Modules\Sales\Entities\SalesActivity');
    }


   /*
   *  Filter salesNotes
   *
   */ 
   public function scopeSalesNotes($query,$sales_notes){

    
       $query->where('sales.sales_notes','ilike','%'.$sales_notes.'%')              
             ->whereIn('id',function($query){
               $query->select(\DB::raw('MAX(id)'))
                     ->from('sales') 
                     ->whereNotNull('sales_notes')
                     ->groupBy('sales_enquiry_id');
            });              

       return $query;
  
   }
/*
    *
    * Sales Notes As List
    */
    public function salesNotesInfo(){

      return $this->hasMany('Modules\Sales\Entities\SalesNote');
    }


/*
* Latest Sales
*
*
*/
 public function scopeLatestSales($query){

    $query->whereIn('id',function($query){
       $query->select(\DB::raw('MAX(id)'))
             ->from('sales')               
             ->groupBy('sales_enquiry_id');
    });
    return $query;

 }


    /*
    *
    * Latest Sales Notes 
    */
    public function salesNotes(){

      return $this->hasOne('Modules\Sales\Entities\Sales','sales_enquiry_id','sales_enquiry_id')
                  ->latest()
                  ->whereNotNull('sales_notes');    
    }



     public function scopeSalesUsers($query){

      $roles = \Auth::user()->getRoles();
      $rolesNames = \Auth::user()->getRoleNames()->toArray();

       $query->whereHas('salesUsers', function ($query) use($roles,$rolesNames) {
                $query->when( (in_array('super_admin', $rolesNames) === false), function($query)use($roles) {
                $query->where(function ($query) use($roles){
                   $query->where(function ($query) use($roles){
                        $query->where('user_id',null)
                              ->whereIn('role_id', $roles)->where('status','=',1);
                      })
                      ->orWhere(function ($query) use($roles){
                        $query->where('user_id','>',0)
                              ->whereIn('role_id', $roles)
                              ->where('user_id','=', \Auth::user()->id)->where('status','=',1);
                      });  
                  });
                })
                ->where('status','=',1);
            });
       
         return $query;

     }
   /*
    *
    *  updatedBy
    *
    */
    public function updatedBy() {
       
           return $this->belongsTo('App\User','updated_by');
    }


 
}
