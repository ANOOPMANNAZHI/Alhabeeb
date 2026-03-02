<?php

namespace Modules\Sales\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use Carbon\Carbon;

class Tenant extends Model
{
    use Sortable;
    protected $guarded = [] ;
    protected $table = 'tenant';
    protected $dates  = ['tenant_resident_exp_date'];
    public $sortable = ['id','tenant_code','tenant_name','tenant_contact_no','tenant_status'];
    
    /*protected $attributes = ['getTenantStatusAttribute'];*/

    /*
    *
    * Tenant Contract
    */
    public function tenantContract(){

      return $this->hasOne('Modules\Sales\Entities\TenantContract','tenant_id');
  	}
    /*
    *
    * Tenant Document
    */
    public function tenantDocument(){

      return $this->hasOne('Modules\Sales\Entities\TenantDocument','tenant_id');
    }
    /*
    *
    * Tenant Type
    */
    public function tenantType(){

      return $this->belongsTo('Modules\Masters\Entities\TenantType');
    }
    /*
    *
    * Tenant Bank Name
    */
    public function bank(){

      return $this->belongsTo('Modules\Masters\Entities\Bank');
    }
    /*
    *
    * Tenant Location
    */
    public function location(){

      return $this->belongsTo('Modules\Masters\Entities\Location');
    }
    /*
    *
    * Tenant Location
    */
    public function nationality(){

      return $this->belongsTo('Modules\Masters\Entities\Nationality','nationalities_id','nationalityid');
    }
    /*
    *
    * Tenant Contracts
    */
    public function tenantContracts(){

      return $this->hasMany('Modules\Sales\Entities\TenantContract','tenant_id');
    }
    /*
    *
    * Tenant Contracts
    */
    public function tenantContractsActive(){

      return $this->hasMany('Modules\Sales\Entities\TenantContract','tenant_id')->where('tenant_contract_status',1);
    }
    
    public function tenantStatusSortable($query, $direction)
    {
        $direction = ($direction == 'asc')? 'desc' : 'asc';
        return $query->orderBy('tenant_status', $direction);
    }
    /*
    * Status Name
    *
    */

    public function gettenantStatusNameAttribute()
    {     
        switch($this->tenant_status){
          case '1' : return 'Active';
          case '0' : return 'Inactive';        
        }
    }
    /*
    * gender
    *
    */

    public function gettenantGenderNameAttribute()
    {     
        switch($this->tenant_gender){
          case '1' : return 'Female';
          case '0' : return 'Male';        
        }
    }
    /*
    *  docs 
    *
    */
      public function tenantDocs(){

      return $this->hasMany('Modules\Masters\Entities\TenantDocs','tenant_id');
    }
     /*
    * Status Name
    *
    */

    public function getStatusNameAttribute()
    {     
        switch($this->status){
          case '1' : return 'VIP';
          case '0' : return 'Normal';        
        }
    }
    /*
    *  Status - Active
    *
    */
    public function scopeActive($query)
    {
        return $query->where('tenant_status', 1);
    } 
    /*
    *  other docs 
    *
    */
      public function tenantOtherDocs(){

      return $this->hasMany('Modules\Masters\Entities\TenantDocs','tenant_id')->where('tenant_doc_category','others');
    }
    /*
    *  other docs 
    *
    */
      public function tenantVisitingDocs(){

      return $this->hasMany('Modules\Masters\Entities\TenantDocs','tenant_id')->where('tenant_doc_category','visiting_card');
    }
    /*
    *
    *
    *Birthday
    *
    */
     public function scopeBirthdays($query)
   {
        return $query->whereRaw("to_char(tenant_date_of_birth, 'MM-DD')::text = '" . date("m-d") . "'");
        //dd($query);
   }
}
