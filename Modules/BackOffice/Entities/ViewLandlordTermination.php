<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class ViewLandlordTermination extends Model
{
	use Sortable;

	protected $table  = 'view_landlord_termination';

    protected $guarded = [];
    
    /*
    * Status Name
    *
    */
    public function getTerminationTypeStatusNameAttribute()
    {     
        switch($this->termination_type_status){
          case '0' : return 'Normal';
          case '1' : return 'Premature';  
          case '2' : return 'Under Approval';
          case '3' : return 'Approved';
          case '4' : return 'Rejected';      
        }
    }

    /*
    *  Termination User
    */
    public function terminationUsers() {
           return $this->belongsToMany('\Spatie\Permission\Models\Role', 'termination_users','termination_id','role_id');
    }


     /*
    *    User Access List 
    */
    public function scopeFilterUsers($query) {

    $roles = \Auth::user()->getRoles();
    $rolesNames = \Auth::user()->getRoleNames()->toArray();    

 
         $query->whereHas('terminationUsers', function ($query) use($roles,$rolesNames) {
         	if (in_array('super_admin', $rolesNames) === false) {
              if (in_array(['are','are_team_lead'], $rolesNames) != true) {

	            $query->where(function ($query) use($roles){
	              $query->where('user_id',null)
	                    ->whereIn('role_id', $roles);
	            })
	            ->orWhere(function ($query) use($roles){
	                $query->where('user_id','>',0)
			              ->whereIn('role_id', $roles)
			              ->where('user_id','=', \Auth::user()->id);
	            });  

	           }
	          }                    
              $query->where('status','=',1);                       
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
		

		foreach($request->except(['_token','sort','direction','page','curr_url','fieldValues','ajax','operation','logic','route']) as $key => $val){         

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
                   else
                     $query->where($key,'ilike','%'.$val.'%'); 
                   
                    
                 }




				}                   

		  }
		}

	}
	return $query;

	} 
}
