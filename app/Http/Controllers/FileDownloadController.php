<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Sales\Entities\TenantDocument;
class FileDownloadController extends Controller
{
	public function download($file_name_id,$documentName) {
		
		switch($documentName){
		case 'tenantContract':
			$doc = TenantDocument::where('id',$file_name_id)->first();
			$filePath = 'app/'.$doc->tenant_documents_file_name;
						
		}
		$file_path = storage_path($filePath);
		return response()->download($file_path);
	  }
     
}
