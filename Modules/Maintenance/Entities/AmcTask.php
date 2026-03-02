<?php

namespace Modules\Maintenance\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
class AmcTask extends Model
{
     use Sortable;
    protected $guarded = [];
    protected $table = 'amc_schedule_task';
    protected $dates = ['amc_schedule_from_date','amc_schedule_to_date'];
    public $sortable = ['id','amc_schedule_id','amenities_type_id','amc_schedule_from_date','amc_schedule_to_date','amc_schedule_task_status','amc_schedule_task_remarks'];  

     /*
    *
    *  Amc Schedule
    */
    public function amcContract(){

      return $this->belongsTo('Modules\Maintenance\Entities\AmcContract','amc_contract_id','id');
    }
    /*
    *
    *  Amc Schedule
    */
    public function amcSchedule(){

      return $this->belongsTo('Modules\Maintenance\Entities\AmcSchedule','amc_schedule_id','id');
    }
    /*
    *
    *  Amenity type
    */
    public function amenityType(){

      return $this->belongsTo('Modules\Masters\Entities\AmentityType','amenities_type_id','id');
    }

    /*
    *
    *  Building
    */
    public function building(){

      return $this->belongsTo('Modules\Masters\Entities\Building');
    }

    /*
    *  amenityType   Scope 
    *
    **/
    public function scopeAmenityType($query,$request){

       if(isset($request->fieldName)){    $i = 0;  $generalSearch = array();

          foreach ($request->fieldName as $keyName => $valueName) {

          $curr_logic =   ($i == 0)? 'and' :$next_logic;
          $next_logic =  $request->logic[$keyName]; $i++;

              if($valueName != 'amenityType__amentity_types_name')   
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
          
          if(count($generalSearch) == 0)
            return $query;
         

           $query->whereHas('amenityType', function ($query) use($generalSearch){
          foreach($generalSearch  as $searchKey => $subSearchVal){

             if($subSearchVal['logic'] == 'and' ){
                if($subSearchVal['operation'] ==  'ilike%...%')
                  $query->where('amentity_types_name','ilike','%'.$subSearchVal['fieldValue'].'%');
                else
                  $query->where('amentity_types_name',$subSearchVal['operation'],$subSearchVal['fieldValue']); 

              }else{
                if($subSearchVal['operation'] ==  'ilike%...%')
                  $query->orWhere('amentity_types_name','ilike','%'.$subSearchVal['fieldValue'].'%');
                else
                  $query->orWhere('amentity_types_name',$subSearchVal['operation'],$subSearchVal['fieldValue']);
             }
           }
          });
       }


       return $query;

    }



       public function scopeClosure($query, $result = array()){
//dd($result);

        $closure =  $closure_or =  $vendor_nameq =  $vendor_name_or =  $contract_no_q = $contract_no_or =$amenities_q =$amenities_or = $building_q = $building_or = $paymentMethod_q = $paymentMethod_or = array();

        // $customer_name, $email , $phone  , $status
        $quick_search_flag = false;
        $qiuck_search  = array();
          // dd($vendor_nameq);
        if(count($result) > 0){
            list($closure, $closure_or,$contract_no_q,$contract_no_or,$vendor_nameq,$vendor_name_or,$amenities_q,$amenities_or,$building_q,$building_or,$paymentMethod_q,$paymentMethod_or,$qiuck_search) =  $result;

        if(count($qiuck_search) > 0)
            $quick_search_flag = true; 
        }
        $query->when($closure, function ($query) use($closure){
        return $query->where($closure);
        })

        ->when($closure_or, function ($query) use($closure_or){
            return $query->orwhere($closure_or);
        })  
        ->when($vendor_nameq, function ($query) use($vendor_nameq){   
        $query->whereHas('amcSchedule', function ($query) use($vendor_nameq){               
            $query->whereHas('vendor', function ($query) use($vendor_nameq){
               foreach($vendor_nameq as $vendor_name_val){                             
                    $query->where('vendor_name', $vendor_name_val[1],$vendor_name_val[2]);
                }   
            }); 
            $query->orwhereHas('technician', function ($query) use($vendor_nameq){
               foreach($vendor_nameq as $vendor_name_val){                             
                    $query->where('username', $vendor_name_val[1],$vendor_name_val[2]);
                }   
            }); 
        });             
        return $query;                                       
        }) 
        ->when($vendor_name_or , function ($query) use($vendor_name_or){ 
        $query->whereHas('amcSchedule', function ($query) use($vendor_name_or){               
            $query->orwhereHas('vendor', function ($query) use($vendor_name_or){
               foreach($vendor_name_or as $vendor_name_or_val){                           
                    $query->orWhere('vendor_name', $vendor_name_or_val[1],$vendor_name_or_val[2]);
                }   
            }); 
            $query->orwhereHas('technician', function ($query) use($vendor_name_or){
               foreach($vendor_name_or as $vendor_name_or_val){                           
                    $query->orWhere('username', $vendor_name_or_val[1],$vendor_name_or_val[2]);
                }   
            }); 
         });             
        return $query;                                       
        })
        ->when($contract_no_q, function ($query) use($contract_no_q){                  
            $query->whereHas('amcSchedule', function ($query) use($contract_no_q){
                $query->whereHas('amcContract', function ($query) use($contract_no_q){
               foreach($contract_no_q as $contract_no_val){                             
                    $query->where('amc_contract_no', $contract_no_val[1],$contract_no_val[2]);
                }
                 });   
            });             
        return $query;                                       
        }) 
        ->when($contract_no_or , function ($query) use($contract_no_or){                
            $query->orwhereHas('amcSchedule', function ($query) use($contract_no_or){
                 $query->whereHas('amcContract', function ($query) use($contract_no_q){
               foreach($contract_no_or as $contract_no_or_val){                           
                    $query->where('amc_contract_no', $contract_no_or_val[1],$contract_no_or_val[2]);
                }   
            }); 
        });             
        return $query;                                       
        })
         ->when($amenities_q, function ($query) use($amenities_q){                  
            $query->whereHas('amenityType', function ($query) use($amenities_q){
               foreach($amenities_q as $amenities_val){                             
                    $query->where('amentity_types_name', $amenities_val[1],$amenities_val[2]);
                }   
            });             
        return $query;                                       
        }) 
        ->when($amenities_or , function ($query) use($amenities_or){                
            $query->orwhereHas('amenityType', function ($query) use($amenities_or){
               foreach($amenities_or as $aminities_or_val){                           
                    $query->where('amentity_types_name', $aminities_or_val[1],$aminities_or_val[2]);
                }   
            });             
        return $query;                                       
        })
        ->when($building_q, function ($query) use($building_q){                  
            $query->whereHas('amcSchedule', function ($query) use($building_q){
                $query->whereHas('building', function ($query) use($building_q){
               foreach($building_q as $building_val){                             
                    $query->where('building_name', $building_val[1],$building_val[2]);
                }
                 });   
            });             
        return $query;                                       
        }) 
        ->when($building_or , function ($query) use($building_or){                
            $query->orwhereHas('amcSchedule', function ($query) use($building_or){
                 $query->whereHas('building', function ($query) use($building_q){
               foreach($building_or as $building_or_val){                           
                    $query->where('building_name', $building_or_val[1],$building_or_val[2]);
                }   
            }); 
        });             
        return $query;                                       
        })

        ->when($paymentMethod_q, function ($query) use($paymentMethod_q){                  
            $query->whereHas('amcSchedule', function ($query) use($paymentMethod_q){
                $query->whereHas('paymentMethod', function ($query) use($paymentMethod_q){
               foreach($paymentMethod_q as $paymentMethod_val){                             
                    $query->where('payment_method_code', $paymentMethod_val[1],$paymentMethod_val[2]);
                }
                 });   
            });             
        return $query;                                       
        }) 
        ->when($paymentMethod_or , function ($query) use($paymentMethod_or){                
            $query->orwhereHas('amcSchedule', function ($query) use($paymentMethod_or){
                 $query->whereHas('paymentMethod', function ($query) use($paymentMethod_q){
               foreach($paymentMethod_or as $paymentMethod_or_val){                           
                    $query->where('payment_method_code', $paymentMethod_or_val[1],$paymentMethod_or_val[2]);
                }   
            }); 
        });             
        return $query;                                       
        })

        ->when($quick_search_flag , function ($query) use($qiuck_search){  
           
           list($amc_schedule_id, $vendor_id,$amenities_types_id,$building_id,$payment_method_id, $amc_schedule_from_date,$amc_schedule_to_date,$amc_schedule_task_status) =   $qiuck_search;
                     
                $query->when($amc_schedule_from_date, function ($query) use($amc_schedule_from_date){                                
                 return $query->whereDate('amc_schedule_from_date','=', $amc_schedule_from_date);                            
                })
                ->when($amc_schedule_to_date, function ($query) use($amc_schedule_to_date){                                
                 return $query->whereDate('amc_schedule_to_date','=', $amc_schedule_to_date);                            
                })
                ->when($amc_schedule_task_status, function ($query) use($amc_schedule_task_status){                                
                 return $query->where('amc_schedule_task_status','=', $amc_schedule_task_status);                            
                })
                ->when($vendor_id , function ($query) use($vendor_id){
                    $query->whereHas('amcSchedule', function ($query) use($vendor_id){
                    $query->whereHas('vendor', function ($query) use($vendor_id){
                        return $query->where('vendor_name','ilike', '%'.$vendor_id.'%');
                    });
                    $query->orwhereHas('technician', function ($query) use($vendor_id){
                        return $query->where('username','ilike', '%'.$vendor_id.'%');
                    });
                });
                    return $query;                     
                })
                ->when($amc_schedule_id , function ($query) use($amc_schedule_id){
                    $query->whereHas('amcSchedule', function ($query) use($amc_schedule_id){
                    $query->whereHas('amcContract', function ($query) use($amc_schedule_id){
                        return $query->where('amc_contract_no','ilike', '%'.$amc_schedule_id.'%');
                    });
                });
                    return $query;                     
                })
                ->when($amenities_types_id , function ($query) use($amenities_types_id){
                    $query->whereHas('amenityType', function ($query) use($amenities_types_id){
                        return $query->where('amentity_types_name','ilike', '%'.$amenities_types_id.'%');
                    });
                    return $query;                     
                })
                ->when($building_id , function ($query) use($building_id){
                    $query->whereHas('amcSchedule', function ($query) use($building_id){
                    $query->whereHas('building', function ($query) use($building_id){
                        return $query->where('building_name','ilike', '%'.$building_id.'%');
                    });
                });
                    return $query;                     
                })
                ->when($payment_method_id , function ($query) use($payment_method_id){
                    $query->whereHas('amcSchedule', function ($query) use($payment_method_id){
                    $query->whereHas('paymentMethod', function ($query) use($payment_method_id){
                        return $query->where('payment_method_code','ilike', '%'.$payment_method_id.'%');
                    });
                });
                    return $query;                     
                });
                
                                                      
        });    
       return $query; 
    }

}
