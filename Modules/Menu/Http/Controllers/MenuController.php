<?php

namespace Modules\Menu\Http\Controllers;

use Modules\Menu\Entities\Menu;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\User;

class MenuController extends Controller
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
     //   $menu = Menu::orderBy('parent_menu','asc')->orderBy('menu_order','asc')->paginate(20);
        $menu = Menu::parentMenu()->orderBy('menu_order','asc')->paginate(20);
        return view('menu::menu_list',compact('menu'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        $menu_list = Menu::menuGroup()->get();
        return view('menu::add_menu',compact('menu_list'));
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {

       $this->validate($request, [
            'menu_name'    => 'required|string',          
            'menu_icon'    => 'required|string',
            'route_name'    => 'required_if:menutype,2|string',        
            'url_key'    => 'required_if:menutype,2|string',
         ]);
        
       if(!isset($request['parent_menu']) || empty($request['parent_menu']))
            $request['parent_menu'] = 0;

        $order_next = Menu::where('parent_menu',$request['parent_menu'])->count();
        $order_next = $order_next + 1;
        
        $menu = Menu::create([
                'menu_name' => $request['menu_name'], 
                'menu_icon' => $request['menu_icon'], 
                'route_name' => $request['route_name'], 
                'url_key' => $request['url_key'], 
                'parent_menu' => $request['parent_menu'], 
                'menutype' => $request['menutype'], 
                'menu_order' => $order_next , 
        ]);        

        session()->flash('success', ' Menu Added ');
        return redirect()->route('menu.index');

    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show()
    {
        return view('menu::add_menu');
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(Menu $menu)
    {
        $menu_list = Menu::menugroup()->get();
         return view('menu::add_menu',compact('menu','menu_list'));
   
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,Menu $menu)
    {
         
        $this->validate($request, [
            'menu_name'    => 'required|string',          
            'menu_icon'    => 'required|string',
            'route_name'    => 'required_if:menutype,2|string',        
            'url_key'    => 'required_if:menutype,2|string',        
                   
         ]);

        if(!isset($request['parent_menu']) || empty($request['parent_menu']))
            $request['parent_menu'] = 0;

        $menu->update([
            'menu_name' => $request['menu_name'], 
            'menu_icon' => $request['menu_icon'], 
            'route_name' => $request['route_name'], 
            'url_key' => $request['url_key'], 
            'parent_menu' => $request['parent_menu'], 
            'menutype' => $request['menutype'], 
        ]);
       

        session()->flash('success', ' Menu Updated ');        
        return redirect()->route('menu.index');       

    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy(Menu $menu)
    {
        try {       
        $menu->delete();
        session()->flash('success', ' Menu Deleted ');
        }
        catch (\Exception $e) {
            session()->flash('error', "Sorry, You can't delete this Menu!");
        }
       
        return redirect()->route('menu.index');
    }


    /*
    * Menu Order 
    *
    **/
    public function menuOrderUpdate(Request $request){

         $id = $request->id;
         $order_type = $request->order_type;          

         $menu = Menu::find($id);
         $menuOrder = $menu->menu_order;

         if($order_type == 'up'){

                Menu::where('menu_order',($menuOrder-1))
                      ->where('parent_menu',$menu->parent_menu)
                      ->update([ 
                       'menu_order' => $menuOrder 
                       ]);             
             $menu->menu_order = $menuOrder -1;

         }else{
              Menu::where('menu_order', ($menuOrder+1))
                    ->where('parent_menu',$menu->parent_menu)
                    ->update([ 
                       'menu_order' => $menuOrder 
                     ]);
              $menu->menu_order = $menuOrder + 1;
         }         
         $menu->save();           
    }



    /*
    *  Menu List
    *
    */
    public function menuList(Menu $menu){

        $parentMenu = $menu;
        
        $menu = Menu::where('parent_menu',$menu->id)->orderBy('menu_order','asc')->paginate(20);
        return view('menu::menu_list',compact('menu','parentMenu'));

    }
    /*
    *  SIdebar user level setting Open or Close 
    *
    */
    public function  sidebarSetting(Request $request){

        $id = $request->id;
        $setSidebar = $request->setSidebar;
       User::where('id', $id)->update(['default_sidebar'=> $setSidebar]);

       return 1;            
	}


}
