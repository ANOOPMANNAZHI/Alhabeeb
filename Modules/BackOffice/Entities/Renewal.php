<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;


class Renewal extends Model
{
	use Sortable;
    protected $guarded = [];
    protected $table = 'renewals';
    public $sortable = ['id','new_contract_id','old_contract_id'];

   
    /*
    *  Renewal User
    */
    public function renewalUsers() {
           return $this->belongsToMany('\Spatie\Permission\Models\Role', 'renewal_users','renewals_id','role_id');
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
    * Old Tenant Contract
    *
    */
    public function oldTenantContract(){

      return $this->belongsTo('Modules\Sales\Entities\TenantContract','old_contract_id','id');
    }
    /*
    *
    * New Tenant Contract
    *
    */
    public function newTenantContract(){

      return $this->belongsTo('Modules\Sales\Entities\TenantContract','new_contract_id','id');
    }
    /*
    *
    * Landlord Contract
    *
    */
    public function landlordContract(){

      return $this->belongsTo('Modules\Sales\Entities\LandlordContract');
    }
    /*
    * 
    *  renewalUser
    *
    */
    public function renewalUser() {
        
           return $this->hasMany('Modules\BackOffice\Entities\RenewalUser','renewals_id');
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
    *  UpdatedBy
    *
    */
    public function updatedByUser() {
        
           return $this->belongsTo('App\User','updated_by');
    }

    /*
    *
    * Old Landlord Contract
    *
    */
    public function oldLandlordContract(){

      return $this->belongsTo('Modules\Sales\Entities\LandlordContract','old_contract_id','id');
    }
    /*
    *
    * New Landlord Contract
    *
    */
    public function newLandlordContract(){

      return $this->belongsTo('Modules\Sales\Entities\LandlordContract','new_contract_id','id');
    }


    /*
    * New Contract   Tenant
    *
    */
    public function scopeNewContract($query,$request){
   
      if(isset($request->new_contract_no)){
        $new_contract_no = $request->new_contract_no;
        $query->whereHas('newTenantContract',function($query) use($new_contract_no){
                 $query->where('tenant_contract_no','ilike','%'.$new_contract_no.'%');
                 return $query;
               });

       }

      $generalSearch = array(); 
      if(isset($request->fieldName)){
          $i = 0; 
          foreach ($request->fieldName as $keyName => $valueName) {

             $curr_logic =  ($i == 0)? 'and' : $next_logic;
              $next_logic =  $request->logic[$keyName]; $i++;
            
            if($valueName != 'tenant_contract_no')              
              continue;
            
              

              if($request->operation[$keyName] == 'ilike%...%' ){
                $fieldValue = '%'.$request->fieldValue[$keyName].'%';
                $operation = 'ilike';
              }else{
              $operation = $request->operation[$keyName];
              $fieldValue = $request->fieldValue[$keyName];
              }

            

              $generalSearch[] = array( 'field'=> $valueName ,'operation'=> $operation , 'fieldValue' => $fieldValue, 'logic'=> $curr_logic);
          }
          $generalSearch = collect($generalSearch)->groupBy('field');

           if(count($generalSearch) == 0)
            return $query;

          $query->whereHas('newTenantContract',function($query) use($generalSearch){

           foreach($generalSearch  as $searchKey => $searchVal){
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
           return $query;
         });



      }           
      
      return $query;
   
    }
    /*
    * Old Contract   Tenant  
    *
    */
    public function scopeOldContract($query,$request){
   
      if(isset($request->tenant_contract_old_no)){
        $old_contract_no = $request->tenant_contract_old_no;
        $query->whereHas('oldTenantContract',function($query) use($old_contract_no){
                 $query->where('tenant_contract_no','ilike','%'.$old_contract_no.'%');
                 return $query;
               });

       }


          $generalSearch = array(); 
      if(isset($request->fieldName)){
          $i = 0; 
          foreach ($request->fieldName as $keyName => $valueName) {

             $curr_logic =  ($i == 0)? 'and' : $next_logic;
              $next_logic =  $request->logic[$keyName]; $i++;
            
            if($valueName != 'tenant_contract_old_no')              
              continue;
            
              $valueName = 'tenant_contract_no';

              if($request->operation[$keyName] == 'ilike%...%' ){
                $fieldValue = '%'.$request->fieldValue[$keyName].'%';
                $operation = 'ilike';
              }else{
              $operation = $request->operation[$keyName];
              $fieldValue = $request->fieldValue[$keyName];
              }

            

              $generalSearch[] = array( 'field'=> $valueName ,'operation'=> $operation , 'fieldValue' => $fieldValue, 'logic'=> $curr_logic);
          }
          $generalSearch = collect($generalSearch)->groupBy('field');

           if(count($generalSearch) == 0)
            return $query;

          $query->whereHas('oldTenantContract',function($query) use($generalSearch){

           foreach($generalSearch  as $searchKey => $searchVal){
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
           return $query;
         });



      }            
      
      return $query;
   
    }


    /*
    * New Contract   Landlord
    *
    */
    public function scopeNewLandlordContract($query,$request){
   
      if(isset($request->landlord_contract_no) && !empty($request->landlord_contract_no)){
        $new_contract_no = $request->landlord_contract_no;
        $query->whereHas('newLandlordContract',function($query) use($new_contract_no){
                 $query->where('landlord_contract_no','ilike','%'.$new_contract_no.'%');
                 return $query;
               });

       }

      $generalSearch = array(); 
      if(isset($request->fieldName)){
          $i = 0; 
          foreach ($request->fieldName as $keyName => $valueName) {

             $curr_logic =  ($i == 0)? 'and' : $next_logic;
              $next_logic =  $request->logic[$keyName]; $i++;
            
            if($valueName != 'landlord_contract_no')              
              continue;
            
              

              if($request->operation[$keyName] == 'ilike%...%' ){
                $fieldValue = '%'.$request->fieldValue[$keyName].'%';
                $operation = 'ilike';
              }else{
              $operation = $request->operation[$keyName];
              $fieldValue = $request->fieldValue[$keyName];
              }

            

              $generalSearch[] = array( 'field'=> $valueName ,'operation'=> $operation , 'fieldValue' => $fieldValue, 'logic'=> $curr_logic);
          }
          $generalSearch = collect($generalSearch)->groupBy('field');

           if(count($generalSearch) == 0)
            return $query;

          $query->whereHas('newLandlordContract',function($query) use($generalSearch){

           foreach($generalSearch  as $searchKey => $searchVal){
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
           return $query;
         });



      }           
      
      return $query;
   
    }
    /*
    * Old Contract  Landlord  
    *
    */
    public function scopeOldLandlordContract($query,$request){
   
      if(isset($request->old_landlord_contract_no)){
        $old_contract_no = $request->old_landlord_contract_no;
        $query->whereHas('oldLandlordContract',function($query) use($old_contract_no){
                 $query->where('landlord_contract_no','ilike','%'.$old_contract_no.'%');
                 return $query;
               });

       }


          $generalSearch = array(); 
      if(isset($request->fieldName)){
          $i = 0; 
          foreach ($request->fieldName as $keyName => $valueName) {

             $curr_logic =  ($i == 0)? 'and' : $next_logic;
              $next_logic =  $request->logic[$keyName]; $i++;
            
            if($valueName != 'old_landlord_contract_no')              
              continue;
            
              $valueName = 'landlord_contract_no';

              if($request->operation[$keyName] == 'ilike%...%' ){
                $fieldValue = '%'.$request->fieldValue[$keyName].'%';
                $operation = 'ilike';
              }else{
              $operation = $request->operation[$keyName];
              $fieldValue = $request->fieldValue[$keyName];
              }

            

              $generalSearch[] = array( 'field'=> $valueName ,'operation'=> $operation , 'fieldValue' => $fieldValue, 'logic'=> $curr_logic);
          }
          $generalSearch = collect($generalSearch)->groupBy('field');

           if(count($generalSearch) == 0)
            return $query;

          $query->whereHas('oldLandlordContract',function($query) use($generalSearch){

           foreach($generalSearch  as $searchKey => $searchVal){
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
           return $query;
         });



      }            
      
      return $query;
   
    }

}
