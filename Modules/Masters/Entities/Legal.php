<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Kyslik\ColumnSortable\Sortable;

class Legal extends Model
{
    use \App\Concerns\LegacyDateSerialization;
	use Sortable;
	protected $guarded = [];
	protected $table = 'legal';
//	public $sortable = ['id','building_id','unit_id','tenant_contract_id','work_flow_processes_code'];

    /*
    *  Legal User
    */
    public function legalUsers() {
    	return $this->belongsToMany('\Spatie\Permission\Models\Role', 'legal_users','legal_id','role_id');
    }
    /*
    *
    * Work Flow Process
    */
    public function workFlowProcess(){

    	return $this->belongsTo('Modules\General\Entities\WorkFlowProcess','work_flow_processes_code','work_flow_processes_code');
    }

    /*
    *
    *building
    *
    *
    */
    public function building(){

    	return $this->belongsTo('Modules\Masters\Entities\Building');
    }
    /*
    *
    *unit
    *
    *
    */
    public function unit(){

      return $this->belongsTo('Modules\Masters\Entities\Unit');
    }
    /*
    *
    *tenant Contract
    *
    *
    */
    public function tenantContract(){

      return $this->belongsTo('Modules\Sales\Entities\TenantContract');
    }
    /*
    * 
    *  legalUser
    *
    */
    public function lawyer() {
        return $this->belongsTo('App\User', 'lawyer_user_id');
    }

    public function legalUser() {
        
           return $this->hasMany('Modules\Masters\Entities\LegalUser','legal_id');
    }

     /*
     * workFlow process class
     */
    public function getWorkFlowProcessesCodeClassAttribute()
    {

    	$code =  $this->work_flow_processes_code; 

    	switch($code){
    		 case 801:
    		           $class = 'label-success';
    		            break;
    		 
    		 case 802: $class = 'label-primary';
    		            break;  

             case 803:  $class = 'label-general';
    		            break; 

    		 case 804: 
    		            $class = 'label-danger';
    		            break;

             default : $class = 'label-warning';
    		            break;
    	}
    	return $class;
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
    *  Legal Document
    *
    */
    public function legalDocument(){

      return $this->hasMany('Modules\Masters\Entities\LegalDocuments','legal_id');
    }
    /*
    * 
    *  Legal Advicer Stage
    *
    */
    public function legalAdvicerNote(){

      return $this->hasOne('Modules\Masters\Entities\LegalNotes','legal_id');
    }

    /*
    * 
    *  Legal Advicer Stage
    *
    */
    public function legalNotes(){

      return $this->hasMany('Modules\Masters\Entities\LegalNotes','legal_id');
    }
    /*
* LegalNotes - End
*
*
*/
public function scopeNoteStatus($query,$request){
  $params = $request->params;

  if(isset($request->params)){
    $query->whereHas('legalNotes',function($query){
     $query->where('legal_notes_status','=',0);
     return $query;
   });
    return $query;
  }
 
}
    /*
* LegalNotes - except End
*
*
*/
public function scopeExceptEndStatus($query,$request){
  $params = $request->param;
  if(isset($request->param)){
    $query->whereHas('legalNotes',function($query){
     $query->where('legal_notes_status','!=',0);
     return $query;
   });
    return $query;
  }
  
}
/*
* Lawyer ReferBack
*
*
*/
public function scopeLawyerReferBack($query,$request){

  if(isset($request->param)){
    $user = \Auth::user();
    if($user->hasRole('legal_advisor')){
       $query->where('work_flow_processes_code',804)
       ->where('are_status',1);
      
    }
  }
  return $query;
}


    /*
    *   Legal ARE building
    *
    */
    public function scopeAreLegalBuilding($query){

       $user = \Auth::user();
       $role_count = count($user->roles);
       $headUser = \Auth::user()->id;

       if($role_count == 1 && $user->hasRole('are')){           
           $query->whereHas('building', function ($query) {
               $query->areFilter();
           }); 
        }
        elseif($role_count == 1 && $user->hasRole('are_team_lead')){

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

      $fieldNameFlag = false;  
      $generalSearch = array(); 
     
    
        foreach($request->except(['_token','sort','direction','page','curr_url','fieldValues','ajax','operation','logic','route','params','param','md','lc_priority','lc_type','lc_stage','lc_lawyer','lc_hearing','lc_ownership']) as $key => $val){         

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

      




    public function scopeClosure($query, $result = array()){


    	$closure =  $closure_or =  $building_nameq = $building_name_or =$unit_nameq =$unit_name_or =$tenant_contractq =$tenant_contract_or =$tenant_nameq =$tenant_name_or =$workflowq =$workflow_or  = array();
        // $customer_name, $email , $phone  , $status
    	$quick_search_flag = false;
    	$qiuck_search  = array();

    	if(count($result) > 0){
    		list($closure, $closure_or,$building_nameq,$building_name_or,$unit_nameq,$unit_name_or,$tenant_contractq,$tenant_contract_or,$tenant_nameq,$tenant_name_or,$workflowq,$workflow_or,$qiuck_search) =  $result;

    		if(count($qiuck_search) > 0)
    			$quick_search_flag = true; 
    	}
    	$query->when($closure, function ($query) use($closure){
    		return $query->where($closure);
    	})

    	->when($closure_or, function ($query) use($closure_or){
    		return $query->orwhere($closure_or);
    	})  
    	->when($building_nameq, function ($query) use($building_nameq){                  
    		$query->whereHas('building', function ($query) use($building_nameq){
    			foreach($building_nameq as $building_name_val){                             
    				$query->where('building_name', $building_name_val[1],$building_name_val[2]);
    			}   
    		});             
    		return $query;                                       
    	}) 
    	->when($building_name_or , function ($query) use($building_name_or){                
    		$query->orwhereHas('building', function ($query) use($building_name_or){
    			foreach($building_name_or as $building_name_or_val){                           
    				$query->where('building_name', $building_name_or_val[1],$building_name_or_val[2]);
    			}   
    		});             
    		return $query;                                       
    	})
    	->when($unit_nameq, function ($query) use($unit_nameq){                  
    		$query->whereHas('unit', function ($query) use($unit_nameq){
    			foreach($unit_nameq as $unit_name_val){                             
    				$query->where('unit_code', $unit_name_val[1],$unit_name_val[2]);
    			}   
    		});             
    		return $query;                                       
    	}) 

    	->when($unit_name_or , function ($query) use($unit_name_or){                
    		return $query->orwhereHas('unit', function ($query) use($unit_name_or){
    			foreach($unit_name_or as $unit_name_or_val){                           
    				return $query->where('unit_code', $unit_name_or_val[1],$unit_name_or_val[2]);
    			}
    			return $query;    
    		});             
    		return $query;                                       
    	})
    	->when($tenant_contractq, function ($query) use($tenant_contractq){                  
    		$query->whereHas('tenantContract', function ($query) use($tenant_contractq){
    			foreach($tenant_contractq as $tenant_contract_val){                             
    				$query->where('tenant_contract_no', $tenant_contract_val[1],$tenant_contract_val[2]);
    			}   
    		});             
    		return $query;                                       
    	}) 
    	->when($tenant_contract_or , function ($query) use($tenant_contract_or){                
    		$query->orwhereHas('tenantContract', function ($query) use($tenant_contract_or){
    			foreach($tenant_contract_or as $tenant_contract_or_val){                           
    				$query->where('tenant_contract_no', $tenant_contract_or_val[1],$tenant_contract_or_val[2]);
    			}   
    		});             
    		return $query;                                       
    	})
    	->when($tenant_nameq, function ($query) use($tenant_nameq){                  
    		$query->whereHas('tenantContract', function ($query) use($tenant_nameq){
    			$query->whereHas('tenant', function ($query) use($tenant_nameq){
    			foreach($tenant_nameq as $tenant_name_val){                             
    				$query->where('tenant_name', $tenant_name_val[1],$tenant_name_val[2]);
    			}   
    		});  
    		});            
    		return $query;                                       
    	}) 
    	->when($tenant_name_or , function ($query) use($tenant_name_or){                
    		$query->orwhereHas('tenantContract', function ($query) use($tenant_name_or){
    		$query->orwhereHas('tenant', function ($query) use($tenant_name_or){
    			foreach($tenant_name_or as $tenant_name_or_val){                           
    				$query->where('tenant_name', $tenant_name_or_val[1],$tenant_name_or_val[2]);
    			}   
    		});             
    		});             
    		return $query;                                       
    	})
    	->when($quick_search_flag , function ($query) use($qiuck_search){  

    		list($tenant_contract_no,$tenant_id,$building_id,$unit_id,$tenant_contract_valid_to_date,$tenant_contract_status,$tenant_contract_rent,$work_flow_processes_id) =   $qiuck_search;

    		$query->when($unit_id , function ($query) use($unit_id){
    			$query->whereHas('unit', function ($query) use($unit_id){
    				return $query->where('unit_code','ilike', '%'.$unit_id.'%');
    			});
    			return $query;                     
    		})
    		->when($building_id , function ($query) use($building_id){
    			$query->whereHas('building', function ($query) use($building_id){
    				return $query->where('building_name','ilike', '%'.$building_id.'%');
    			});
    			return $query;                     
    		})->when($tenant_id , function ($query) use($tenant_id){
    			$query->whereHas('tenantContract', function ($query) use($tenant_id){
    			$query->whereHas('tenant', function ($query) use($tenant_id){
    				return $query->where('tenant_name','ilike', '%'.$tenant_id.'%');
    			});
    			});
    			return $query;                     
    		})
    		->when($tenant_contract_no , function ($query) use($tenant_contract_no){
    			$query->whereHas('tenantContract', function ($query) use($tenant_contract_no){
    				return $query->where('tenant_contract_no','ilike', '%'.$tenant_contract_no.'%');
    			});
    			return $query;                     
    		})
    		->when($tenant_contract_valid_to_date , function ($query) use($tenant_contract_valid_to_date){
    			$query->whereHas('tenantContract', function ($query) use($tenant_contract_valid_to_date){
    				return $query->where('tenant_contract_valid_to_date','ilike', '%'.$tenant_contract_valid_to_date.'%');
    			});
    			return $query;                     
    		})

    		->when(($tenant_contract_status != '') , function ($query) use($tenant_contract_status){
    			$query->whereHas('tenantContract', function ($query) use($tenant_contract_status){
    				return $query->where('tenant_contract_status','ilike', '%'.$tenant_contract_status.'%');
    			});
    			return $query;                    
    		})
    		->when($tenant_contract_rent , function ($query) use($tenant_contract_rent){
    			$query->whereHas('tenantContract', function ($query) use($tenant_contract_rent){
    				return $query->where('tenant_contract_rent','ilike', '%'.$tenant_contract_rent.'%');
    			});
    			return $query;                     
    		})
    		->when($work_flow_processes_id , function ($query) use($work_flow_processes_id){
    			$query->whereHas('workFlowProcess', function ($query) use($work_flow_processes_id){
    				return $query->where('work_flow_processes_name','ilike', '%'.$work_flow_processes_id.'%');
    			});
    			return $query;                     
    		})
    		->when($tenant_contract_no , function ($query) use($tenant_contract_no){
    			$query->whereHas('tenantContract', function ($query) use($tenant_contract_no){
    				return $query->where('tenant_contract_no','ilike', '%'.$tenant_contract_no.'%');
    			});
    			return $query;                     
    		});

    	});    
    	return $query; 
    }
public function scopeLegalUsers($query){    

      $roles = \Auth::user()->getRoles();
      $rolesNames = \Auth::user()->getRoleNames()->toArray();
      
      // user_id / role_id / status are legal_users columns: look them up per case. The old code put them
      // straight on "legal", so every role outside the list below got "column user_id does not exist".
      $query->when( (\Auth::user()->hasRole(['super_admin','ceo','backoffice_manager','legal_advisor','backoffice_executive']) == false), function($query)use($roles) {
          $query->whereExists(function ($query) use($roles){
                   $query->select(DB::raw(1))->from('legal_users')->whereColumn('legal_users.legal_id', 'legal.id')
                   ->where(function ($query) use($roles){
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
             });
           });
               
       
         return $query;

     
  }

    /**
     * Lists open with the most recently moved to legal first (user, 18/10/2026); a column the user
     * clicks to sort on (?sort=) still wins. The legal row is created when the case is moved.
     */
    public static function newestMovedFirst($query)
    {
        if (!request()->filled('sort')) {
            $query->orderBy('legal.created_at', 'desc')->orderBy('legal.id', 'desc');
        }
        return $query;
    }

    /**
     * Legal lists show one row per tenant + building + unit: the case of the latest contract
     * (renewals each get their own case). $base is the list's filtered query; sort + paginate the result.
     */
    public static function latestPerGroup($base)
    {
        $ids = (clone $base)->select('legal.id');
        return static::whereIn('legal.id', function ($q) use ($ids) {
            $q->select('g.id')
              ->from(DB::raw('(select l2.id, row_number() over (partition by tc2.tenant_id, l2.building_id, l2.unit_id'
                  . ' order by tc2.tenant_contract_valid_to_date desc nulls last, tc2.id desc, l2.id desc) as rn'
                  . ' from legal l2 join tenant_contracts tc2 on tc2.id = l2.tenant_contract_id'
                  . ' where l2.id in (' . $ids->toSql() . ')) as g'))
              ->mergeBindings($ids->getQuery())
              ->whereRaw('g.rn = 1');
        });
    }

    /**
     * Sets relation groupSiblings on each row of the page: the other cases of its group that are in
     * the same list ($base), latest contract first.
     */
    public static function attachGroupSiblings($page, $base)
    {
        $rows = $page->getCollection();
        if ($rows->isEmpty()) {
            return $page;
        }
        $rows->load('tenantContract');
        $key = function ($c) {
            return ($c->tenantContract ? $c->tenantContract->tenant_id : 0) . '-' . $c->building_id . '-' . $c->unit_id;
        };
        $siblings = static::with(['tenantContract.tenant', 'building', 'unit', 'workFlowProcess'])
            ->whereIn('legal.id', (clone $base)->select('legal.id'))
            ->whereIn('legal.unit_id', $rows->pluck('unit_id')->unique()->values()->all())
            ->whereNotIn('legal.id', $rows->pluck('id')->all())
            ->get()
            ->sort(function ($a, $b) {
                $da = $a->tenantContract ? (string) $a->tenantContract->tenant_contract_valid_to_date : '';
                $db = $b->tenantContract ? (string) $b->tenantContract->tenant_contract_valid_to_date : '';
                return $da === $db ? $b->id - $a->id : strcmp($db, $da);
            })
            ->groupBy($key);
        foreach ($rows as $c) {
            $c->setRelation('groupSiblings', $siblings->get($key($c), collect())->values());
        }
        return $page;
    }
}
