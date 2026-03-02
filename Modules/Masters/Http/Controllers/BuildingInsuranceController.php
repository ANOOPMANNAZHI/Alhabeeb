<?php

namespace Modules\Masters\Http\Controllers;

use Modules\Masters\Entities\BuildingInsurance;
use Modules\Masters\Entities\Building;
use Carbon\Carbon;
use Session;
use URL;
use Route;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;
use Image;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

class BuildingInsuranceController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth'); 
    $this->middleware('permission:add_building_insurance', ['only' => ['create','store']]);  
    $this->middleware('permission:edit_building_insurance', ['only' => ['edit','update']]);  
    $this->middleware('permission:delete_building_insurances', ['only' => ['destroy']]);  
    $this->middleware('permission:change_building_insurance', ['only' => ['changeStatus']]);  
    $this->middleware('permission:view_building_insurance', ['only' => ['index','show']]);          
  }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {
      $fieldName =  $request->fieldName;
      $fieldValue = $request->fieldValue;  

      $noOfRecord  	= prefixData('no_of_records_in_list_grid')->configuration_value; 
	  $insurance 	= BuildingInsurance::when($fieldValue, function ($query) use($fieldValue,$fieldName){

            if($fieldName == 'insurance_company'){
                      $query->where($fieldName,'ilike', '%'.$fieldValue.'%');
            
            }elseif($fieldName == 'building_name'){
                $query->whereHas('building', function ($query) use($fieldValue,$fieldName) {
                $query->where($fieldName,'ilike', '%'.$fieldValue.'%');
                  });
            }
            return $query;
           })->sortable()->paginate($noOfRecord);
           
      $fields = [
      'insurance_company' => 'Insurance Company',  
      'building_name' => 'Building Name',

      ];
      $request->flash();  

      $buildings = Building::active()->get();
      return view('masters::BuildingInsurance.list', compact('insurance','buildings','fields','request'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create(Request $request)
    {

     $backIdBreadCrumb  = ($request->id)?$request->id:null;
     $backUrlBreadCrumb = ($request->id)?$request->backurl:'building-insurance.index';

     $previousUrl = ($request->id)?route($backUrlBreadCrumb,$request->id).'?tab=insurance':'building-insurance.index'; 
     $buildings = Building::active()->get();

     return view('masters::BuildingInsurance.add_edit',compact('buildings', 'backIdBreadCrumb', 'backUrlBreadCrumb','previousUrl'));
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
      if(isset($request->backurl))
       $url = $request->backurl;
	
     $this->validate($request, [
      'insurance_company' => 'required|unique:building_insurance',           
      'building_id' => 'required',           
      'insurance_policy_type' => 'required',           
      'insurance_premium_value' => 'required',      
      'insurance_start' => 'required|date|before:insurance_end',           
      'insurance_end' => 'required|date|after:insurance_start',           
      'insurance_building_value'=>'required',           
      'insurance_insured_by' => 'required',           
      ]);   

     if(!empty($request->file('insurance_img_copy'))):
      $path = public_path('img');
    $imageName = time().'.'.$request['insurance_img_copy']->getClientOriginalExtension();


    $large_img = Image::make($request->file('insurance_img_copy')->getRealPath());
    $large_img->resize(800, 500);
    $large_img->save($path.'/'.$imageName,100);
    $img_path =    Storage::putFile('public/InsuranceCopyImg', new File($path.'/'.$imageName), 'public');

    $thumb_img = Image::make($request->file('insurance_img_copy')->getRealPath());
    $thumb_img->resize(150, 100);
    $thumb_img->save($path.'/'.$imageName,100);           
    $thumb_path = Storage::putFile('public/InsuranceCopyImg', new File($path.'/'.$imageName), 'public');
    endif;
    $buildingInsurance = BuildingInsurance::create([
      'insurance_company' => $request->insurance_company,      
      'building_id' => $request->building_id,          
      'insurance_policy_type' => $request->insurance_policy_type,           
      'insurance_premium_value' => $request->insurance_premium_value,
      'insurance_building_value' =>  $request->insurance_building_value,
      'insurance_start' => $request->insurance_start,          
      'insurance_end' => $request->insurance_end,       
      'insurance_insured_by' => $request->insurance_insured_by,        
      'insurance_debit_acc' => $request->insurance_debit_acc,
      'insurance_img_copy' => $img_path ?? '',
      'insurance_path_thumbnail'=>$thumb_path ?? '',
      'created_by' =>  \Auth::user()->id,           
      ]);

      // Log insertion
    activity('Add Building Insurance')
    ->performedOn($buildingInsurance)
    ->causedBy(\Auth::user()->id)
    ->withProperties($buildingInsurance)
    ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

    session()->flash('success', ' Building Insurance Added ');
    return redirect($url);  
  }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {
      $buildings = Building::active()->get();
      $insurance = BuildingInsurance::where('id',$id)->first();
      return view('masters::BuildingInsurance.view',compact('insurance','buildings'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(Request $request, BuildingInsurance $buildingInsurance)
    {
      
      $backIdBreadCrumb  = ($request->backid)?$request->backid:null;
      $backUrlBreadCrumb = ($request->backurl)?$request->backurl:'building-insurance.index';
      
      $previousUrl = ($request->backid)?route($backUrlBreadCrumb,$request->backid).'?tab=insurance':'building-insurance.index'; 
     	
      $buildings = Building::active()->get();
      return view('masters::BuildingInsurance.add_edit',compact('buildingInsurance','previousUrl','buildings','backIdBreadCrumb', 'backUrlBreadCrumb'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request, BuildingInsurance $buildingInsurance)
    {
      if(isset($request->backurl))
       $url = $request->backurl;

      $this->validate($request, [
		  'insurance_company' => 'required',           
		  'building_id' => 'required',           
		  'insurance_policy_type' => 'required',           
		  'insurance_premium_value' => 'required',      
		  'insurance_start' => 'required|date|before:insurance_end',           
		  'insurance_end' => 'required|date|after:insurance_start',           
		  'insurance_building_value'=>'required',           
		  'insurance_insured_by' => 'required',           
		  ]);   

     if(!empty($request->file('insurance_img_copy'))):
      $path = public_path('img');
    $imageName = time().'.'.$request['insurance_img_copy']->getClientOriginalExtension();


    $large_img = Image::make($request->file('insurance_img_copy')->getRealPath());
    $large_img->resize(800, 500);
    $large_img->save($path.'/'.$imageName,100);
    $img_path =    Storage::putFile('public/InsuranceCopyImg', new File($path.'/'.$imageName), 'public');

    $thumb_img = Image::make($request->file('insurance_img_copy')->getRealPath());
    $thumb_img->resize(150, 100);
    $thumb_img->save($path.'/'.$imageName,100);           
    $thumb_path = Storage::putFile('public/InsuranceCopyImg', new File($path.'/'.$imageName), 'public');
    endif;
    if(empty($request->file('insurance_img_copy'))):
    $img_path = $buildingInsurance->insurance_img_copy ?? '';
    $thumb_path = $buildingInsurance->insurance_path_thumbnail ?? '';
    endif;

    $buildingInsurance->update([
      'insurance_company' => $request->insurance_company,      
      'building_id' => $request->building_id,          
      'insurance_policy_type' => $request->insurance_policy_type,           
      'insurance_premium_value' => $request->insurance_premium_value,
      'insurance_building_value' =>  $request->insurance_building_value,
      'insurance_start' => $request->insurance_start,          
      'insurance_end' => $request->insurance_end,       
      'insurance_insured_by' => $request->insurance_insured_by,        
      'insurance_debit_acc' => $request->insurance_debit_acc,
      'insurance_img_copy' => $img_path ?? '',
      'insurance_path_thumbnail'=>$thumb_path ?? '',
      'created_by' =>  \Auth::user()->id,           
      ]);

       // Log insertion
    activity('Update Building Insurance')
    ->performedOn($buildingInsurance)
    ->causedBy(\Auth::user()->id)
    ->withProperties($buildingInsurance)
    ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

    session()->flash('success', 'Building Insurance successfully updated');
    return redirect($url);
  }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy(BuildingInsurance $buildingInsurance)
    {
      $url = url()->previous();
		try {
                                
           $buildingInsurance->delete();

            session()->flash('success', 'Building Insurance Deleted Successfully');
        }
        catch (\Exception $e) {
            session()->flash('error', 'Please delete related records before');
        }
        // Log
        activity('Deleted Building Insurance')
          ->performedOn($buildingInsurance)
          ->causedBy(\Auth::user()->id)
          ->withProperties($buildingInsurance)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
       
        return redirect($url);
    }
    
  }
