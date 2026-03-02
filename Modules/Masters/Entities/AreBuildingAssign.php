<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
class AreBuildingAssign extends Model
{
	use Sortable;
	protected $guarded = [];
	protected $table = 'are_buildings';
	protected $dates = ['assign_from','assign_to'];
	public $sortable = ['id','user_id','building_id','assign_from','assign_to'];  




	/*
    *  Building Names
    */
    public function buildingNames() {
           return $this->belongsToMany('\Modules\Masters\Entities\Building', 'preferred_buildings','are_building_id','building_id')->withPivot('building_id'); 
     }

    /*
	*  Building Names 
	*/
	public function buildingNamesExist() {
	       return $this->belongsToMany('\Modules\Masters\Entities\Building', 'preferred_buildings','are_building_id','building_id')->withPivot('building_id')->where('assign_to',null); 
	 }
	  
	/*
    *
    *  Building
    */
    public function building(){

      return $this->belongsTo('Modules\Masters\Entities\Building');
    }
    public function scopeUserHeader(){ 

        return $this->hasMany('Modules\Masters\Entities\employee','head_user','user_id');
    }
    /*
    *
    *  Are
    */
     public function areUser(){
      return $this->belongsTo('App\User','user_id','id');
    } 

    /*
    *user
    *
    */
    public function user(){
        
        /*return $this->belongsToMany('Modules\Masters\Entities\Employee','are_buildings','user_id','head_user');*/
        return $this->belongsTo('App\User','user_id','id');
        
    }
    /*
    *
    *  assigned buildings
    */
        public function assignedBuildingNames() {
           return $this->belongsToMany('\Modules\Masters\Entities\Building', 'preferred_buildings','are_building_id','building_id')->where('assign_to',null)->withPivot('building_id'); 
     } 

      public function assignedBuildingDate() {
           return $this->belongsToMany('\Modules\Masters\Entities\Building', 'preferred_buildings','are_building_id','building_id')->where('assign_to',null)->withPivot('assign_from'); 
     }

    /*
    *
    *  closure
    */
    public function scopeClosure($query, $result = array()){


        $closure =  $closure_or =  $user_nameq =  $user_name_or  = $building_nameq =  $building_name_or  = array();
        
        $quick_search_flag = false;
        $qiuck_search  = array();
   
        if(count($result) > 0){
            list($closure, $closure_or,$user_nameq,$user_name_or,$building_nameq,$building_name_or,$qiuck_search) =  $result;
        
        if(count($qiuck_search) > 0)
            $quick_search_flag = true; 
        }
        $query->when($closure, function ($query) use($closure){
        return $query->where($closure);
        })

        ->when($closure_or, function ($query) use($closure_or){
            return $query->orwhere($closure_or);
        })  
        ->when($user_nameq, function ($query) use($user_nameq){                  
            $query->whereHas('areUser', function ($query) use($user_nameq){
               foreach($user_nameq as $user_name_val){                             
                    $query->where('username', $user_name_val[1],$user_name_val[2]);
                }   
            });             
        return $query;                                       
        }) 
        ->when($user_name_or , function ($query) use($user_name_or){                
            $query->orwhereHas('areUser', function ($query) use($user_name_or){
               foreach($user_name_or as $user_name_or_val){                           
                    $query->where('username', $user_name_or_val[1],$user_name_or_val[2]);
                }   
            });             
        return $query;                                       
        })
        ->when($building_nameq, function ($query) use($building_nameq){                  
            $query->whereHas('assignedBuildingNames', function ($query) use($building_nameq){
               foreach($building_nameq as $building_name_val){                             
                    $query->where('building_name', $building_name_val[1],$building_name_val[2]);
                }   
            });             
        return $query;                                       
        }) 
        ->when($building_name_or , function ($query) use($building_name_or){                
            $query->orwhereHas('assignedBuildingNames', function ($query) use($building_name_or){
               foreach($building_name_or as $building_name_or_val){                           
                    $query->where('building_name', $building_name_or_val[1],$building_name_or_val[2]);
                }   
            });             
        return $query;                                       
        })

        ->when($quick_search_flag , function ($query) use($qiuck_search){  
           
           list($user_id,$building_id, $assign_from) =   $qiuck_search;
                     
                $query->when($assign_from, function ($query) use($assign_from){                                
                 return $query->whereDate('assign_from','=', $assign_from);                            
                })
                ->when($user_id , function ($query) use($user_id){
                    $query->whereHas('areUser', function ($query) use($user_id){
                        return $query->where('username','ilike', '%'.$user_id.'%');
                    });
                    return $query;                     
                })
                ->when($building_id , function ($query) use($building_id){
                    $query->whereHas('assignedBuildingNames', function ($query) use($building_id){
                        return $query->where('building_name','ilike', '%'.$building_id.'%');
                    });
                    return $query;                     
                });
                                                      
        });    
       return $query; 
    }

}
