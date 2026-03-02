<?php

namespace Modules\Menu\Entities;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{

	protected $table = 'menu';
	
	protected $guarded = [];
     
    
    //Menu Type 1 - Menu Group   
    //          2 - Menu     
    public function scopeParentMenu($query)
    {
        return $query->where('parent_menu', 0);
    } 


    public function scopeMenuGroup($query)
    {
        return $query->where('menutype', 1);
    }


    public function permissionList(){

       return $this->hasMany('Spatie\Permission\Models\Permission','menu_id');

     }


     /*
     * menuGroup
     *
     */
     public function menuGroupName(){

        return $this->belongsTo('Modules\Menu\Entities\Menu','parent_menu')->withDefault();
     }




     /*
      * Child Menu  
      *
      */ 
      public function allChildMenu(){
		  
		    return $this->hasMany('Modules\Menu\Entities\Menu','parent_menu','id')->with('allChildMenu')
		                ->permissionMenu(); 
		}
		/*
    * Child Menu  without permission menu
    *
    */ 
    public function ChildMenus(){
      
        return $this->hasMany('Modules\Menu\Entities\Menu','parent_menu','id'); 
    }
	/*	
		 ->where(function ($query) {
							$query->where('menutype', 1)
								   ->has('allChildMenu', '>=', 1);
								 //  ->with('allChildMenu');								  
							return $query;
					         }) ;
		             //   ->permissionMenu(); 
*/
      
      /*
       * Scope Permissions 
       * 
       */

     public function scopePermissionMenu($query)
        {
			
	  $res =  \Auth::user()->getAllPermissions()->pluck('menu_id')->toArray();
      $res = array_unique($res); 
			
                   
       return $query->where('menutype', 1)
                    ->orWhere(function ($query)use($res) {
							$query->where('menutype', 2)->where('status',1)
								  ->whereIn('id', $res);
								  
							return $query;
					});
        }	
     





    

}
