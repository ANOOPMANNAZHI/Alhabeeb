<?php

namespace Modules\Masters\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Modules\Masters\Entities\Work;
use Modules\Masters\Entities\SubWork;
use DB;
use Session;
use URL;
use Route;

class SubWorkController extends Controller
{
   public function __construct()
   {
    $this->middleware('auth'); 
}
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {
		$fieldName = $request->fieldName;
        $fieldValue = $request->fieldValue;  
        $noOfRecord  = prefixData('no_of_records_in_list_grid')->configuration_value; 
        $subWorks = SubWork::when($fieldValue, function ($query) use($fieldValue,$fieldName){

            if($fieldName == 'works_id'){
                $query->whereHas('work', function ($query) use($fieldValue,$fieldName) {
                $query->where('works_code','ilike', '%'.$fieldValue.'%');
                  });
            }elseif($fieldName == 'sub_work'){
                
                $query->where($fieldName,'ilike', '%'.$fieldValue.'%');
                  
            }
            return $query;
           })->sortable()->paginate($noOfRecord);
        
       
        //$subWorks=SubWork::get();
        //dd($subWorks);
        $fields = [
           'sub_work' => 'Sub Work',     
           'works_id' => 'Work',               
        ];        
        $request->flash(); 
        
        return view('masters::SubWork.list',compact('subWorks','fields','request'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
      $Work = Work::get();
      return view('masters::SubWork.add_edit',compact('Work'));
  }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
       $this->validate($request, [
        'works_id'    => 'required',                    
        'sub_work'   => 'required'
        ]);
       $work =  SubWork::create([
          'works_id' => $request['works_id'],
          'sub_work' => $request['sub_work'],
          'created_by' => \Auth::user()->id,
          ]);
       session()->flash('success', 'Sub Work Created Successfully');
       return redirect()->route('subWork.index');
   }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {
       $subWork = SubWork::where('id',$id)->first();
        return view('masters::SubWork.view',compact('subWork'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(Request $request,SubWork $subWork)
    {
        $previousUrl = url()->previous();
        
        $backIdBreadCrumb  = ($request->backid)?$request->backid:null;
        $backUrlBreadCrumb = ($request->backurl)?$request->backurl:'subWork.index';
        $SubWork = SubWork::get();  
        $Work = Work::get();
        return view('masters::SubWork.add_edit',compact('subWork','Work','previousUrl','backIdBreadCrumb','backUrlBreadCrumb'));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request,SubWork $subWork)
    {
        $this->validate($request, [
            'works_id'    => 'required',                    
            'sub_work'   => 'required'
            ]);
        SubWork::where('id',$subWork->id)
        ->update([
            'works_id' => $request['works_id'],
            'sub_work' =>$request['sub_work'], 
            'updated_by' => \Auth::user()->id
            ]); 
        session()->flash('success', 'Sub Work Updated Successfully');
        return redirect()->route('subWork.index');
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy(SubWork $subWork)
    {
         try {
                                
            $subWork->delete();

            session()->flash('success', 'SubWork Deleted Successfully');
        } 
        catch (\Exception $e) {
            session()->flash('error', 'Please delete related records before');
        }
         return redirect()->route('subWork.index');
    }
}
