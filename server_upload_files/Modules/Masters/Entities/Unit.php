<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use Carbon\Carbon;

class Unit extends Model
{
	use Sortable;
	
    protected $guarded = [];

    protected $dates = [ 'unit_legal_date'];
    // Every column offered as a @sortablelink in Unit/list.blade.php must appear
    // here or ColumnSortable ignores the click without any error.
    public $sortable = ['id','unit_code','unit_no','vacant_status','unit_vaccant_status','unit_status'];


    /*
  	* Building
  	*
  	*/
  	  public function building(){

      return $this->belongsTo('Modules\Masters\Entities\Building','building_id');
  	}


    /*
  	* Unit Type
  	*
  	*/
  	  public function unit(){

      return $this->belongsTo('Modules\Masters\Entities\UnitType','unit_type_id');
  	}


  	/*
    * Vacant Status Name
    *
    */

    public function getVacantStatusNameAttribute()
    {     
        switch($this->unit_vaccant_status){
          case '0' : return 'Vacant';
          case '1' : return 'Occupied';        
          case '2' : return 'Vacating';         
        }
    }



    /*
    * Status Name
    *
    */

    public function getUnitStatusNameAttribute()
    {     
        switch($this->unit_status){
          case '1' : return 'Active';
          case '0' : return 'Inactive';        
        }
    }

   /*
    * Furnished
    *
    */

    public function getUnitIsFurnishedNameAttribute()
    {     
        switch($this->unit_is_furnished){
          case '1' : return 'Furnished';
          case '2' : return 'Unfurnished';        
        }
    }

    /*
    * unit_is_legal_ans
    *
    */

    public function getUnitIsLegalAnsAttribute()
    {     
        switch($this->unit_is_legal){
          case '1' : return 'Yes';
          case '2' : return 'No';        
        }
    }
    
    
    /*
    *  Status - Active
    *
    */
    public function scopeActive($query)
    {
        return $query->where('unit_status', 1);
    }  
    
    
    
    
    
     public function unitStatusSortable($query, $direction)
		{
			$direction = ($direction == 'asc')? 'desc' : 'asc';
			return $query->orderBy('unit_status', $direction);
		}

/*
    *
    * Tenant Contract
    */
    public function tenantContract(){

      return $this->hasOne('Modules\Sales\Entities\TenantContract','unit_id','id')->where('tenant_contract_status',1);
    }

/*
*
*Key
*
*/
 public function key(){

      return $this->hasOne('Modules\BackOffice\Entities\Key','unit_id','id')->where('status','=',1);;
    }

  /*
  *  Scope are
  *
  */
  public function scopeAre($query){


    $user = \Auth::user();
    $role_count = count($user->roles);
    $headUser = \Auth::user()->id;

    if($role_count == 1 && $user->hasRole('are')){
       $query->whereHas('building', function ($query){
           $query->whereHas('areBuildings', function ($query){
             $query->where('user_id','=', \Auth::user()->id);
             return $query;
           });

       });
    }elseif($role_count == 1 && $user->hasRole('are_team_lead')){

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
           
      foreach($request->except(['_token','sort','direction','page','curr_url','vaccating_days','vacantUnits_byDays']) as $key => $val){

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

                   // ILIKE only works on text columns. Applying it to an integer
                   // column (unit_type_id, building_id, the status flags) makes
                   // Postgres abort with
                   //   operator does not exist: integer ~~* unknown
                   // so those are matched exactly instead. It is also what the
                   // caller means: picking unit type 2 should not match 12 or 20.
                   if(strpos($key, 'status') !== false || substr($key, -3) === '_id')
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
   * Abvance Filter 
   *
   */
   public function scopeAdvanceFilter($query, $request){

      if(isset($request)){     

      $fieldNameFlag = false;  
      $generalSearch = array();     
    
        foreach($request->except(['_token','sort','direction','page','curr_url','fieldValues','ajax','operation','logic','route','vacantUnits_byDays']) as $key => $val){         

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
                  $val =  $request->fieldValue;
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
                   else
                    $query->where($key,'ilike','%'.$val.'%');
                }

               }                   
               
             }
        }
          
      }
      return $query;

   }


    
    



   public function scopeClosure($query, $result = array()){


    $closure =  $closure_or =  $building =  $building_or = $code = $code_or = $type = $type_or = array();

        // $customer_name, $email , $phone  , $status
    $quick_search_flag = false;
    $qiuck_search  = array();

    if(count($result) > 0){
      list($closure, $closure_or,$building,$building_or,$code , $code_or,$type , $type_or, $qiuck_search) =  $result;

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
    ->when($code, function ($query) use($code){               
      $query->whereHas('building', function ($query) use($code){
       foreach($code as $code_val){                             
        $query->where('building_code', $code_val[1],$code_val[2]);                 
      } 
      return $query;   
    });             
      return $query;                                       
    }) 
    ->when($code_or , function ($query) use($code_or){                
      $query->orwhereHas('building', function ($query) use($code_or){
       foreach($code_or as $code_or_val){                           
        $query->orWhere('building_code', $code_or_val[1],$code_or_val[2]);
      }   
    });             
      return $query;                                       
    })
    ->when($type, function ($query) use($type){               
      $query->whereHas('building', function ($query) use($type){
        $query->whereHas('buildingType', function ($query) use($type){
          foreach($type as $type_val){                             
            $query->where('building_types_name', $type_val[1],$type_val[2]);                 
          } 
          return $query;   
        }); 
      });             
      return $query;                                       
    }) 
    ->when($type_or , function ($query) use($type_or){                
      $query->orwhereHas('building', function ($query) use($type_or){
        $query->orwhereHas('buildingType', function ($query) use($type_or){
         foreach($type_or as $type_or_val){                           
          $query->orWhere('building_types_name', $type_or_val[1],$type_or_val[2]);
        }   
      }); 
      });            
      return $query;                                       
    })
    ->when($quick_search_flag , function ($query) use($qiuck_search){  

     list($contract_no, $tenant_contract_old_no,$building_id , $unit_id, $tenant_contract_start_date, $tenant_contract_valid_to_date,$tenant_id,$tenant_contract_rent) =   $qiuck_search;

     $query->when($contract_no, function ($query, $contract_no) {
      return $query->where('tenant_contract_no', 'ilike',  '%'.$contract_no.'%');
    })
     ->when($tenant_contract_old_no, function ($query, $tenant_contract_old_no) {
      return $query->where('tenant_contract_no', 'ilike',  '%'.$tenant_contract_old_no.'%');
    })
     ->when($building_id, function ($query, $building_id) {
      $query->whereHas('building', function ($query) use($building_id){
        return $query->where('building_name','ilike', '%'.$building_id.'%');
      });
      return $query; 
    })     
     ->when($unit_id , function ($query) use($unit_id){
      $query->whereHas('Unit', function ($query) use($unit_id){
        return $query->where('unit_code','ilike', '%'.$unit_id.'%');
      });
      return $query;                     
    })

     ->when($tenant_contract_start_date, function ($query, $tenant_contract_start_date) {
      return $query->whereDate('tenant_contract_start_date', '=',  $tenant_contract_start_date);
    })  
     ->when($tenant_contract_valid_to_date , function ($query) use($tenant_contract_valid_to_date){
      return $query->whereDate('tenant_contract_valid_to_date', '=',  $tenant_contract_valid_to_date);                     
    })
     ->when($tenant_id , function ($query) use($tenant_id){
      $query->whereHas('tenant', function ($query) use($tenant_id){
        return $query->where('tenant_name','ilike', '%'.$tenant_id.'%');
      });
      return $query;                     
    })
     ->when($tenant_contract_rent, function ($query, $tenant_contract_rent) {
      return $query->where('tenant_contract_rent', '=',  $tenant_contract_rent);
    });


   });                         

    return $query; 
  }
  /*
  *
  * units vacating in 15 days
  *
  *
  */
public function scopeVaccatingUnits($query,$request){
  $vaccatingDays = $request->vaccating_days;
  $nextDays = Carbon::today()->addDays($vaccatingDays);
  $today = date('Y-m-d');
  if($vaccatingDays){
   $query->whereHas('tenantContract', function ($query)use($nextDays,$today){
           $query->whereHas('terminationContract', function ($query)use($nextDays,$today){
             $query->where('termination_date', '!=',null)->whereDate('termination_date','<=',$nextDays)->whereDate('termination_date','>=',$today);
             return $query;
           });

       });
  }
   return $query;
}
 /*
  *
  * units vacating in 15 days
  *
  *
  */
public function scopeVaccatingUnit($query){
  $vaccatingDays = 15;
  $nextDays = Carbon::today()->addDays($vaccatingDays);
  $today = date('Y-m-d');
   $query->whereHas('tenantContract', function ($query)use($nextDays,$today){
           $query->whereHas('terminationContract', function ($query)use($nextDays,$today){
             $query->where('termination_date', '!=',null)->whereDate('termination_date','<=',$nextDays)->whereDate('termination_date','>=',$today);
             return $query;
           });

       });
   return $query;
}

/*****    vacantUnits_byDays      ***********/
public function scopeVacantUnitsByDays($query,$request){

  if(isset($request->vacantUnits_byDays)){
   $day = $request->vacantUnits_byDays;

    $query->where('unit_vaccant_status',0)          
          ->orWhere(function($query)use($day) {
              $query->where('unit_vaccant_status',0)
                    ->whereHas('tenantContract', function($query) use($day){
                      $query->whereDate('tenant_contract_valid_to_date', '<=', Carbon::now()->subDays($day)->toDateString())
                            ->where('tenant_contract_status',0)
                            ->doesntHave('tenantContractOld');
                        });
                });

  }

  return $query;
 }
/*
    *
    * Tenant Contract all
    */
    public function allTenantContract(){

      return $this->hasOne('Modules\Sales\Entities\TenantContract','unit_id','id');
    }
  	
}
