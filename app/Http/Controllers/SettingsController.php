<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Menu\Entities\Menu;
use App\Setting;
use Spatie\Permission\Models\Role;

class SettingsController extends Controller
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
	 * Show the settings.
	 *
	 * @return \Illuminate\Http\Response
	 */
     public function index(Request $request, $tab =null)
     {	
       $tab = $request->tab;
       //dd($tab);
       $config_data = Setting::orderBy('id')->get();
       return view('setting.general_config', compact('config_data','tab'));
   }
     /**
	 * Show the settings.
	 *
	 * @return \Illuminate\Http\Response
	 */
     public function store(Request $request)
     {	
       
       $config_data = Setting::where('configuration_name',$request->config)->get();
              
       switch($request->config)
       {

         case 'general':
         
         foreach($config_data as $item){
           $requestField = $item->configuration_settings;
           $configuration_year = $item->configuration_year;
           $configuration_increment_value = $item->configuration_increment_value;
    			 
           $requestValue = $request->$requestField;
           $configurationYear = $request->{$requestField.'_year'};
           $configurationIncrementValue = $request->{$requestField.'_increment_value'};
            
           $config_data = Setting::find($item->id);
           $config_data['configuration_value'] = $requestValue;
           $config_data['configuration_year'] = $configurationYear;
           $config_data['configuration_increment_value'] = $configurationIncrementValue;
           $config_data->save(); 
       }
       break;

       case 'email':
       
       foreach($config_data as $item){
           $requestField = $item->configuration_settings;
    					//dd($request->landlord_agreement_prefix);
           $requestValue = $request->$requestField;
    					//$val = $request->$item->configuration_settings;
           $config_data = Setting::find($item->id);
           $config_data['configuration_value'] = $requestValue;
           $config_data->save(); 
       }
       break;

       case 'sms':
       
       foreach($config_data as $item){
           $requestField = $item->configuration_settings;
    					//dd($requestField);
           $requestValue = $request->$requestField;
    					
           $config_data = Setting::find($item->id);
           $config_data['configuration_value'] = $requestValue;
           $config_data->save(); 
       }
       
       break;
       case 'settings':
       
       foreach($config_data as $item){
        $requestField = $item->configuration_settings;
        $requestValue = $request->$requestField;
                        //dd($requestValue);
                        //$val = $request->$item->configuration_settings;
        $config_data = Setting::find($item->id);
        $config_data['configuration_value'] = $requestValue;
        $config_data->save(); 
    }
    
    break;
}

session()->flash('success', 'Successfully updated');
return redirect()->route('settings.index',['tab'=>$request->config]);
}


}
