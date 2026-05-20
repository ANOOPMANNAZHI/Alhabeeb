<?php

namespace Modules\Menu\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Modules\Menu\Entities\Menu;

class RoleController extends Controller
{
     /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
       $roles = Role::get();
       return view('menu::role_list',compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('menu::add_role');
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {

        $this->validate($request, [
            'name'    => 'required|string',          
         ]);
      
        Role::create(['name' => str_slug($request['name'],'_')]);

        session()->flash('success', ' Role Added ');
        return redirect()->route('role.index');

    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show()
    {
        return view('menu::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(Role $role)
    {
        return view('menu::add_role',compact('role'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,Role $role)
    {

        $this->validate($request, [
            'name'    => 'required|string',          
        ]);

        $role->update(['name' => str_slug($request['name'],'_')]);
        session()->flash('success', ' Role Updated ');
        return redirect()->route('role.index');

    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy(Role $role)
    {

         $role->delete();

        session()->flash('success', ' Role Deleted ');
        return redirect()->route('role.index');
    }


    /*
    *  Set Role Permission  $role->givePermissionTo
    *
    */
    public function setPermission(Role $role){      

       $menu = Menu::parentMenu()->get();

       return view('menu::set_permission',compact('role','menu'));

    }

    /*
    *  Set Role Permission  
    *
    */
    public function storePermission(Request $request,Role $role){
        
        $menu =  Menu::find($request['menu']);
      
        $menu_list = Menu::where('parent_menu',$menu->id)->get();
   
        foreach($menu_list as $val){  


            if($val->menutype == 1 && count($val->allChildMenu) > 0 ){

                $this->children($val,$role);


            }elseif($val->menutype == 2){

                $permission = $val->permissionList;
                if(!empty($permission)){
                    $permission =  $permission->toArray(); 
                    $permission  = array_pluck($permission, 'name'); 
                    $role->revokePermissionTo($permission); 
                }   

            }           
                      
        }
    
      if (!empty($request['permissions'])) {
          $role->givePermissionTo($request['permissions']);
      }
      app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

      session()->flash('success', ' Permission Assigned ');
      return back();

    }

    /*
    * menuPermissions
    *
    */  
    public function menuPermissions(Request $request,Menu $menu){

		$permission =null;
        if($menu->menutype == 2){
            $permission = $menu->permissionList;

        }else{
            
            $menu = Menu::where('parent_menu',$menu->id)->get();

        }
 
        $role = Role::find($request['role']);

       return view('menu::permissionAssign',compact('permission','menu','role'));

    }
    /*
     *
     * Sub Menus By permission
     *
     */
    public function children($menu,$role){


        foreach($menu->allChildMenu as $val){
            
               if($val->menutype == 1 && count($val->allChildMenu) > 0 ){

                $this->children($val,$role);


              }elseif($val->menutype == 2){

                 $permission = $val->permissionList;
                    if(!empty($permission)){
                        $permission =  $permission->toArray(); 
                        $permission  = array_pluck($permission, 'name'); 
                        $role->revokePermissionTo($permission); 
                    }   

              }            
            
        }
    }


}
