<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use App\User;

class Key extends Model
{
    use Sortable;
    
 	protected $guarded = [];
    protected $table = 'key';
    protected $dates = ['created_at'];
    public $sortable = ['id'];
    /*
    *  Building
    */
    public function building(){

      return $this->belongsTo('Modules\Masters\Entities\Building','building_id','id');
    }

    /*
    * unit
    */
    public function unit(){
    
      return $this->belongsTo('Modules\Masters\Entities\Unit','unit_id','id');
    }
    /*
    * user
    */
    public function user(){
    
      return $this->belongsTo(User::class,'user_id','id');
    }
    /*
    * tenant
    */
    public function tenant(){
    
      return $this->belongsTo('Modules\Sales\Entities\Tenant','tenant_id','id');
    }
    /*
    * Landlord
    */
    public function landlord(){
    
      return $this->belongsTo('Modules\Masters\Entities\Vendor','landlord_id','id');
    }
    /*
    * Status Name
    *
    */

    public function getStatusNameAttribute()
    {     
        switch($this->status){
          case '1' : return 'key-In-Hand';
          case '0' : return 'Handover';        
        }
    }
    /*
    * Search   
    *
    *
    *
    */
    public function scopeClosure($query, $result = array()){


        $closure =  $closure_or =  $building =  $building_or =  $unit = $unit_or  = array();
     
        // $customer_name, $email , $phone  , $status
        $quick_search_flag = false;
        $qiuck_search  = array();
   
        if(count($result) > 0){
            list($closure, $closure_or,$building,$building_or,$unit,$unit_or, $qiuck_search) =  $result;
        
        if(count($qiuck_search) > 0)
            $quick_search_flag = true; 
        }

       //dd($result) ; exit;
        
        $query->when($closure, function ($query) use($closure){
        return $query->where($closure);
        })
        ->when($closure_or, function ($query) use($closure_or){
            return $query->orwhere($closure_or);
        })  
        ->when($building, function ($query) use($building){               
            $query->whereHas('building', function ($query) use($building){
               foreach($building as $building_val){                             
                    $query->where('building_name', $building_val[1],$building_val[2]);                 
                } 
                return $query;   
            });             
        return $query;                                       
        }) 
        ->when($building_or , function ($query) use($building_or){                
            $query->orwhereHas('building', function ($query) use($building_or){
               foreach($building_or as $building_or_val){                           
                    $query->orWhere('building_name', $building_or_val[1],$building_or_val[2]);
                }   
            });             
        return $query;                                       
        })
        ->when($unit, function ($query) use($unit){                  
            $query->whereHas('unit', function ($query) use($unit){
               foreach($unit as $unit_val){                             
                    $query->where('unit_code', $unit_val[1],$unit_val[2]);
                }   
            });             
        return $query;                                       
        }) 
        ->when($unit_or , function ($query) use($unit_or){                
            $query->orwhereHas('unit', function ($query) use($unit_or){
               foreach($unit_or as $unit_or_val){                           
                    $query->orWhere('unit_code', $unit_or_val[1],$unit_or_val[2]);
                }   
            });             
        return $query;                                       
        })
        ->when($quick_search_flag , function ($query) use($qiuck_search){  
           
           list($building_id , $unit_id) =   $qiuck_search;

                $query->when($building_id, function ($query, $building_id) {
                    $query->whereHas('building', function ($query) use($building_id){
                        return $query->where('building_name','ilike', '%'.$building_id.'%');
                    });
                    return $query; 
                })->when($unit_id , function ($query) use($unit_id){
                    $query->whereHas('Unit', function ($query) use($unit_id){
                        return $query->where('unit_code','ilike', '%'.$unit_id.'%');
                    });
                    return $query;                     
                });
                    
                                                      
        });                         
       
       return $query; 
    }



    

  

   /*
   *
   *  Filter 
   *
   */
   public function scopeFilter($query, $request){

      if(isset($request)){     

      $fieldNameFlag = false;  
      $generalSearch = array(); 
     
    
        foreach($request->except(['_token','sort','direction','page','curr_url','fieldValues','ajax','operation','logic','route','params','param']) as $key => $val){         

           if($key == 'fieldValue' || $key == 'fieldValues' )
              continue;

           if($key == 'fieldName' && $fieldNameFlag == true) 
             continue;

             if($val != ''){  

              if($key == 'fieldName'){

                  if(is_array($request->fieldName)){
                    $fieldNameFlag = true;                 


                         $i = 0; 
                         foreach ($request->fieldName as $keyName => $valueName) {



                               if( !empty($request->fieldValue[$keyName]) && !empty($request->fieldValue[$keyName]) && !empty($valueName) ) {
                                 
                                  $curr_logic =  ($i == 0)? 'and' : $next_logic;
                                  $next_logic =  $request->logic[$keyName]; $i++;

                                 
                                  if($request->operation[$keyName] == 'ilike%...%' ){
                                    $fieldValue = '%'.$request->fieldValue[$keyName].'%';
                                    $operation = 'ilike';
                                  }else{
                                  $operation = $request->operation[$keyName];
                                  $fieldValue = $request->fieldValue[$keyName];
                                  }
                                 

                                  $generalSearch[] = array( 'field'=> $valueName ,'operation'=> $operation , 'fieldValue' => $fieldValue, 'logic'=> $curr_logic); 

                               }

                              }

                                                   
                            $generalSearch = collect($generalSearch)->groupBy('field');

                           
                  }else{ 
                  $key = $val;                  
                  $val = ($request->fieldName != 'tenant_contract_status') ? $request->fieldValue : $request->fieldValues;
                }
               } 

              if($key == 'fieldName' && is_array($request->fieldName)){ 

                    foreach($generalSearch  as $searchKey => $searchVal){
                
                       if(strpos($searchVal[0]['field'], '__') !== false) {

                            $method = explode('__',$searchVal[0]['field']);

                            if(count($method) > 2)
                            {
                              $searchMethod = $method[0].'.'. $method[1];
                              $searchfor = $method[2];
                            }else{
                              $searchMethod = $method[0];
                              $searchfor = $method[1];
                            }

                            $query->whereHas($searchMethod, function ($query) use($searchMethod,$searchVal,$searchfor){

                                 foreach($searchVal as $subSearchVal ){
                                  if($subSearchVal['logic'] == 'and' ){

                                    if($subSearchVal['operation'] ==  'ilike%...%')
                                      $query->where($searchfor,'ilike','%'.$subSearchVal['fieldValue'].'%');
                                    else
                                      $query->where($searchfor,$subSearchVal['operation'],$subSearchVal['fieldValue']); 

                                  }else{
                                    if($subSearchVal['operation'] ==  'ilike%...%')
                                      $query->orWhere($searchfor,'ilike','%'.$subSearchVal['fieldValue'].'%');
                                    else
                                      $query->orWhere($searchfor,$subSearchVal['operation'],$subSearchVal['fieldValue']);
                                  }                             

                                 }                                 
                            });

                        }else{

                           foreach($searchVal as $subSearchVal ){
                              if($subSearchVal['logic'] == 'and' ){

                                if($subSearchVal['operation'] ==  'ilike%...%')
                                  $query->where($subSearchVal['field'],'ilike','%'.$subSearchVal['fieldValue'].'%');
                                else
                                  $query->where($subSearchVal['field'],$subSearchVal['operation'],$subSearchVal['fieldValue']); 

                              }else{
                                if($subSearchVal['operation'] ==  'ilike%...%')
                                  $query->orWhere($subSearchVal['field'],'ilike','%'.$subSearchVal['fieldValue'].'%');
                                else
                                  $query->orWhere($subSearchVal['field'],$subSearchVal['operation'],$subSearchVal['fieldValue']);
                              }                             

                           }
                            
                        } 

                    }                
               }else{

                 if(strpos($key, '__') !== false) {

                    $method = explode('__',$key);

                    $query->whereHas($method[0], function ($query) use($method,$val){
                       if(count($method)>2){
                         $query->whereHas($method[1], function ($query) use($method,$val){
                           return $query->where($method[2],'ilike', '%'.$val.'%');
                         });
                       }else
                        return $query->where($method[1],'ilike', '%'.$val.'%');
                    });                   

                }else{

                   if(strpos($key, 'status') !== false) 
                     $query->where($key,$val); 
                    

                    $query->where($key,'ilike','%'.$val.'%'); 
                   
                    
                }

               }                   
               
             }
        }
          
      }
      return $query;

   }

      
/*
* 
*Scope count TakenOverTeam 
*
*/
public function scopeTakenOverTeam($query,$request){
  $userId = User::role(['take_over_supervisor','take_over_executive'])->pluck('id');
  $takenover = $request->params;
  if(isset($request->params)){
    $query->whereIn('user_id',$userId);
    return $query;
  }
  return $query;
}  
 /*
* Scope count Maintenance Engineer
*
*
*/
public function scopeMaintenanceEngineer($query,$request){
  $userId = User::role(['maintenance_engineer'])->pluck('id');
  $takenover = $request->param;
  if(isset($request->param)){
    $query->whereIn('user_id',$userId);
    return $query;
  }
  return $query;
} 
/**   Facility Manager   Scope ****/
    public function scopeFacilityManager($query){

      $user = \Auth::user();

      if($user->hasRole('facility_manager')){
         
         $query->whereHas('user.userRoles',function($query){           
            $query->whereIn('name',['maintenance_coordinator',
              'maintenance_supervisor',
              'maintenance_contracts_executive',
              'maintenance_executive','maintenance_engineer','take_over_executive','take_over_supervisor']);          
          })
          ->whereDate('created_at', '<', \Carbon\Carbon::now()->subDays(6)->toDateString()) ;
      }

      return $query;
    }





}
