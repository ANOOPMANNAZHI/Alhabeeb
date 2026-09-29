<?php

namespace Modules\BackOffice\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\BackOffice\Entities\MessageLog;

/** Mail -> Sent log: every system email / SMS attempt. */
class MessageLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view_message_log');
    }

    public function index(Request $request)
    {
        $logs = MessageLog::orderBy('id', 'desc')->paginate(50);
        return view('backoffice::MessageLog.index', compact('logs'));
    }
}
