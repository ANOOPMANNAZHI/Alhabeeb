<?php

namespace Modules\Menu\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

use Spatie\Permission\Models\Permission;
use Modules\Menu\Entities\Menu;

class PermissionController extends Controller
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
    public function index(Menu $menu)
    {
        $permissions = Permission::where('menu_id',$menu->id)->get();   
        return view('menu::permission_list',compact('permissions','menu'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create(Menu $menu)
    {
         return view('menu::add_permission',compact('menu'));
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request,Menu $menu)
    {
        $this->validate($request, [
            'name'    => 'required|string|unique:permissions',          
         ]);


       $name = str_slug($request['name'],'_');


        try {

         Permission::findByName($name);
         session()->flash('error', ' Permission Key Already Exist');
        }
        catch (\Exception $e) {

           $permission = Permission::create(['name' => $name,'menu_id' => $menu->id]);
            session()->flash('success', ' Permission Key Created');
        }  

         if(!isset($permission))
            session()->flash('error', ' Permission Key Already Exist');
       
        return redirect()->route('menu.permission.index',[$menu->id]);
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
    public function edit(Menu $menu,Permission $permission)
    {
       return view('menu::add_permission',compact('menu','permission'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,Menu $menu, Permission $permission)
    {
        $this->validate($request, [
            'name'    => 'required|string',          
         ]);

        $permission->update(['name' => str_slug($request['name'],'_'),'menu_id' => $menu->id]);

        session()->flash('success', ' Permission Key Updated');
        return redirect()->route('menu.permission.index',[$menu->id]);
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy()
    {
    }
}
