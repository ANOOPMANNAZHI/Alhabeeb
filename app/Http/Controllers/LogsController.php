<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;
use App\User;
use App\Log;
class LogsController extends Controller
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
    	$logs = Activity::with('subject', 'causer')->orderBy('id', 'DESC')->paginate(15);
		
        return view('logs.logs_view', compact('logs'));
    }
}
