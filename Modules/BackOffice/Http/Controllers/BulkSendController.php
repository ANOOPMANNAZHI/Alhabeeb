<?php

namespace Modules\BackOffice\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Modules\BackOffice\Entities\BulkSend;
use Modules\BackOffice\Entities\BulkSendRecipient;
use Modules\BackOffice\Services\Bulk\BulkSendService;

/** Mail -> Send: bulk messages to tenants, landlords or staff. */
class BulkSendController extends Controller
{
    const PER_PAGE = 50;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:bulk_message_send');
    }

    public function index()
    {
        $sends = BulkSend::with(['template', 'creator'])->orderBy('id', 'desc')->paginate(self::PER_PAGE);
        return view('backoffice::BulkSend.index', compact('sends'));
    }

    public function show(Request $request, BulkSend $bulkSend)
    {
        $status = $request->input('status');
        $q = $bulkSend->recipients()->orderBy('id');
        if ($status && in_array($status, ['pending', 'sent', 'failed', 'skipped', 'cancelled'], true)) {
            $q->where('status', $status);
        }
        $recipients = $q->paginate(self::PER_PAGE)->appends(['status' => $status]);
        return view('backoffice::BulkSend.show', ['send' => $bulkSend, 'recipients' => $recipients, 'status' => $status]);
    }

    public function pause(BulkSend $bulkSend)
    {
        BulkSendService::pause($bulkSend);
        return redirect()->route('bulk-send.index')->with('success', 'Send #' . $bulkSend->id . ' paused');
    }

    public function resume(BulkSend $bulkSend)
    {
        BulkSendService::resume($bulkSend);
        return redirect()->route('bulk-send.index')->with('success', 'Send #' . $bulkSend->id . ' resumed');
    }

    public function cancel(BulkSend $bulkSend)
    {
        BulkSendService::cancel($bulkSend);
        return redirect()->route('bulk-send.index')->with('success', 'Send #' . $bulkSend->id . ' cancelled');
    }

    public function resendFailed(BulkSend $bulkSend)
    {
        try {
            $new = BulkSendService::resendFailed($bulkSend, \Auth::id());
        } catch (ValidationException $e) {
            return redirect()->route('bulk-send.index')->with('error', collect($e->errors())->flatten()->first());
        }
        return redirect()->route('bulk-send.index')->with('success', 'Send #' . $new->id . ' started for ' . $new->total . ' failed message(s)');
    }
}
