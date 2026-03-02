<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class Building extends Model
{
	use Sortable;
	
    protected $guarded = [];
    protected $date = ['management_date'];
    
    public $sortable = ['id','building_name','building_code','building_status'];

  /*
    * Status Name
    *
    */

    public function getBuildingStatusNameAttribute()
    {     
        switch($this->building_status){
          case '1' : return 'Active';
          case '0' : return 'Inactive';        
        }
    }
  /*
    * Maintenance Info
    *
    */

    public function getMaintenanceInfoNameAttribute()
    {     
        switch($this->building_maintenance_info){
          case '1' : return 'Not Managed by us';
          case '0' : return 'Managed by us';        
        }
    }

    /*
  	* Vendor
  	*
  	*/
  	  public function vendor(){

      return $this->belongsTo('Modules\Masters\Entities\Vendor','vendor_id');
  	}


  	/*
  	*
  	* BuildingType
  	**/
  	public function buildingType(){

  		 return $this->belongsTo('Modules\Masters\Entities\BuildingType','building_type_id');
  	}


  	/*
  	* Management  
  	*
  	**/
  	public function management(){

  		 return $this->belongsTo('Modules\Masters\Entities\ManagementType','management_id');
  	}
    /*
    * ARE  
    *
    **/
    public function are(){

       return $this->belongsTo('App\User','user_id');
    }
	
    /*
    * Building assign to Are  
    *
    **/
    public function buildingAssignTo(){

       return $this->hasMany('Modules\Masters\Entities\PreferredBuilding','building_id');
    }
    /*
    * Building assign to Are Name 
    *
    **/
    public function buildingAssignToAre(){

       return $this->hasOne('Modules\Masters\Entities\PreferredBuilding','building_id')->where('assign_to',null);
    }
  	/*
    *  Location
    *
    */
      public function buildingLocation(){

      return $this->belongsTo('Modules\Masters\Entities\Location','location_id');
    }
    /*
    * Status Name
    *
    */

    public function getBuildingMaintenanceInfoNameAttribute()
    {     
        switch($this->building_maintenance_info){
          case '1' : return 'Managed by us';
          case '0' : return 'Not Managed by us.';        
        }
    }


    

    /*
    *  Status - Active
    *
    */
    public function scopeActive($query)
    {
        return $query->where('building_status', 1);
    }   

	/*
    *  Status - Inactive
    *
    */
    public function scopeInactive($query)
    {
        return $query->where('building_status', 0);
    }
    
     public function buildingStatusSortable($query, $direction)
		{
			$direction = ($direction == 'asc')? 'desc' : 'asc';
			return $query->orderBy('building_status', $direction);
		}

 
  public function unitTypeCount()
    {
        return $this->belongsToMany('Modules\Masters\Entities\UnitType','unit_type_count','building_id','unit_type_id')->withPivot('unittype_count');
    }  

    /*
    *  Image 
    *
    */
      public function buildingImage(){

      return $this->hasMany('Modules\Masters\Entities\BuildingImage','building_id');
    }
    /*
    *  Image 
    *
    */
      public function buildingDocs(){

      return $this->hasMany('Modules\Masters\Entities\BuildingDocs','building_id');
    }
    /*
    *
    * Tenant Location
    */
    public function location(){

      return $this->belongsTo('Modules\Masters\Entities\Location','location_id');
    }
    /*
    *  ElE Water Category 
    *
    */
      public function buildingEleWaterReading(){

      return $this->hasMany('Modules\Masters\Entities\BuildingEleWaterReading','building_id');
    }
    /*
    *
    *
    * Contract landlord
    *
    */
    public function landlordContract(){

      return $this->hasMany('Modules\Sales\Entities\LandlordContract','building_id');
    }
    /*
    *
    *
    * Contract landlord
    *
    */
    public function landlordContractActive(){

      return $this->hasOne('Modules\Sales\Entities\LandlordContract','building_id')->active();
    }
    /*
    *  Are Preferred buildings 
    *
    */
    public function areBuildings() {

       return $this->belongsToMany('Modules\Masters\Entities\AreBuildingAssign', 'preferred_buildings','building_id','are_building_id')->withPivot('building_id')->where('assign_to',null);
       
 }
 /*
    *
    *
    * unit
    *
    */
    public function unit(){
		
      return $this->hasMany('Modules\Masters\Entities\Unit','building_id')->orderBy('id','asc');
	  
    }
    /*
    *  Image 
    *
    */
      public function buildingDefaultImage(){

      return $this->hasOne('Modules\Masters\Entities\BuildingImage','building_id');
    }
     /*
    *
    *Tenant Contract
    *
    *
    */
    public function tenantContract(){

      return $this->hasOne('Modules\Sales\Entities\TenantContract','building_id','id');
    }

/*
    *
    *Unit
    *
    *
    */
    public function unitInfo(){

      return $this->hasOne('Modules\Masters\Entities\Unit','building_id','id');
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
           $query->whereHas('areBuildings', function ($query){
             $query->where('user_id','=', \Auth::user()->id);
             return $query;
           });       
    }elseif($role_count == 1 && $user->hasRole('are_team_lead')){
          $query->whereHas('areBuildings', function ($query)use($headUser){
            $query->whereHas('user', function ($query)use($headUser){
            $query->whereHas('employee', function ($query)use($headUser){
              $query->where('head_user','=', $headUser);
            });
            });
            $query->orWhere('user_id','=', \Auth::user()->id);
            return $query;
          });

    }

   return $query;
  }





  /*
   *
   *  Filter 
   *
   */
   public function scopeFilter($query, $request){

      if(isset($request)){          
           
        foreach($request->except(['_token','sort','direction','page','curr_url','swipe']) as $key => $val){

           if($key == 'fieldValue')
              continue;

             if($val != ''){  

              if($key == 'fieldName'){
                  $key = $val;
                  $val = $request->fieldValue;
               }      
 
                if(strpos($key, '__') !== false) {

                    $method = explode('__',$key);

                    $query->whereHas($method[0], function ($query) use($method,$val){
                        return $query->where($method[1],'ilike', '%'.$val.'%');
                    });

                }else{

                   if(strpos($key, 'status') !== false) 
                     $query->where($key,$val); 
                   else
                     $query->where($key,'ilike','%'.$val.'%'); 
                }
                                     
               
             }
        }
          
      }
      return $query;

   }
/*
    *
    *  ComplaintEnquiry
    */
    public function complaintEnquiry(){

      return $this->hasMany('Modules\Maintenance\Entities\ComplaintEnquiry');
    }

}
