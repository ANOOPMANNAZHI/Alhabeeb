<?php

namespace Modules\BackOffice\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\BackOffice\Imports\PdcExcelImport;
use Modules\BackOffice\Entities\Pdc;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class TenantPdcExcelController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('backoffice::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('backoffice::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('backoffice::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('backoffice::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    /**
    * @return \Illuminate\Support\Collection -------Upload Excel
    */
    public function import(Request $request) 
    {
		$request->validate([
            'file' => 'required|mimes:csv,txt|max:2048',
            'tenant_contract_id' => 'required',
        ]);
        Excel::import(new PdcExcelImport($request),request()->file('file'));
           
        return back()->with('success', 'Excel ImportedSuccessfully.');
    }
    /*
    *
    *Download Excel
    *
    */
    public function export(){
   //    return  redirect()->route('home');
        return response()->download(storage_path('app/public/ExportExcel/sample-excel.csv'));
    }
}
