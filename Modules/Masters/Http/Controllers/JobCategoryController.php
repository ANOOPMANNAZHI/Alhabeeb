<?php

namespace Modules\Masters\Http\Controllers;

use Modules\Masters\Entities\JobCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class JobCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');     
        $this->middleware('permission:add_currency', ['only' => ['create','store']]);  
        $this->middleware('permission:edit_currency', ['only' => ['edit','update']]);  
        $this->middleware('permission:delete_currency', ['only' => ['destroy']]);  
        $this->middleware('permission:change_status_currency', ['only' => ['changeStatus']]);  
        $this->middleware('permission:view_currency', ['only' => ['index','show']]);     
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {
		//$msg = "Testing SMS";
      //  $params = 'optional data';
		//$mobile='0096895223906';
		//$rss=sendSms($mobile,$msg,$params);
	//	dd($rss);
       $fieldName = $request->fieldName;
        $fieldValue = $request->fieldValue;       
        $noOfRecord  = prefixData('no_of_records_in_list_grid')->configuration_value; 
        $jobCategories = JobCategory::when($fieldValue, function ($query) use($fieldValue,$fieldName){
                                        return $query->where($fieldName,'ilike', '%'.$fieldValue.'%')
                                        ;
                                       })->sortable()->paginate($noOfRecord);                                       
        $fields = [
           'job_category_code' => 'Code',                 
           'job_category_name' => 'Name',                 
        ];
        $request->flash();
                                               
        return view('masters::JobCategory.list',compact('jobCategories','fields','request'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('masters::JobCategory.add_edit');
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'job_category_code'    => 'required|unique:job_category',                    
            'job_category_name'   => 'required'
         ]);
        $jobCategory =  JobCategory::create([
          'job_category_code' => $request['job_category_code'],
          'job_category_name' => $request['job_category_name'],
          'created_by' => \Auth::user()->id,
        ]);
        //Log
        activity('Add JobCategory')
          ->performedOn($jobCategory)
          ->causedBy(\Auth::user()->id)
          ->withProperties($jobCategory)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
        session()->flash('success', 'Job Category Created Successfully');
        return redirect()->route('jobCategory.index');
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {
        $jobCategory = JobCategory::where('id',$id)->first();
        return view('masters::JobCategory.view',compact('jobCategory'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(Request $request,JobCategory $jobCategory)
    {
        $previousUrl = url()->previous();
        
        $backIdBreadCrumb  = ($request->backid)?$request->backid:null;
        $backUrlBreadCrumb = ($request->backurl)?$request->backurl:'currency.index';
        
        return view('masters::JobCategory.add_edit',compact('jobCategory','previousUrl','backIdBreadCrumb','backUrlBreadCrumb'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,JobCategory $jobCategory)
    {
        if(isset($request->backurl))
            $url = $request->backurl;
            
        $this->validate($request, [
            'job_category_code'    => 'required|unique:job_category,job_category_code,'.$jobCategory->id ,                    
            'job_category_name'   => 'required'     
         ]);
         $jobCategory->update([
          'job_category_code' => $request['job_category_code'],
          'job_category_name' => $request['job_category_name'],
          'updated_by' => \Auth::user()->id,
        ]);
        //Log
        activity('Update Job Category')
          ->performedOn($jobCategory)
          ->causedBy(\Auth::user()->id)
          ->withProperties($jobCategory)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
        session()->flash('success', 'Job Category Updated Successfully');
        return redirect($url);
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy(JobCategory $jobCategory)
    {
        try {
                                
            $jobCategory->delete();

            session()->flash('success', 'Job Category Deleted Successfully');
        }
        catch (\Exception $e) {
            session()->flash('error', 'Please delete related records before');
        }
        
        activity('Deleted Job Category')
          ->performedOn($jobCategory)
          ->causedBy(\Auth::user()->id)
          ->withProperties($jobCategory)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
          
        return redirect()->route('jobCategory.index');
    }
    /**
     * Change the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeStatus($id,JobCategory $jobCategory)
    {
        $status = JobCategory::find($id);
        if($status['job_category_status']==1) {
            $status->job_category_status = 0;
        } else {
            $status->job_category_status = 1;
        } 
        $status->save(); 
        
        activity('Change Job Category')
         ->performedOn($status)
          ->causedBy(\Auth::user()->id)
          ->withProperties($status)
          ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
 
     
        session()->flash('success', 'Status Changed Successfully');
        return redirect()->route('jobCategory.index');
    }
}
