<?php

namespace Modules\BackOffice\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\BackOffice\Entities\MessageLog;
use Modules\BackOffice\Entities\MessageTemplate;

/** Mail -> Sent log: every system email / SMS attempt, newest first. */
class MessageLogController extends Controller
{
    const PER_PAGE = 50;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view_message_log');
    }

    public function index(Request $request)
    {
        $validDate = function ($d) { return is_string($d) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) ? $d : null; };
        $filters = [
            'from'     => $validDate($request->input('from')) ?: date('Y-m-d', strtotime('-6 days')),
            'to'       => $validDate($request->input('to')) ?: date('Y-m-d'),
            'template' => trim((string) $request->input('template')),
            'channel'  => in_array($request->input('channel'), ['email', 'sms'], true) ? $request->input('channel') : '',
            'status'   => array_key_exists((string) $request->input('status'), MessageLog::STATUSES) ? $request->input('status') : '',
            'q'        => trim((string) $request->input('q')),
        ];

        $q = MessageLog::query()
            ->where('created_at', '>=', $filters['from'] . ' 00:00:00')
            ->where('created_at', '<', date('Y-m-d', strtotime($filters['to'] . ' +1 day')) . ' 00:00:00');
        if ($filters['template'] !== '') { $q->where('template_key', $filters['template']); }
        if ($filters['channel'] !== '') { $q->where('channel', $filters['channel']); }
        if ($filters['status'] !== '') { $q->where('status', $filters['status']); }
        if ($filters['q'] !== '') {
            $like = '%' . $filters['q'] . '%';
            $q->where(function ($w) use ($like) {
                $w->where('recipient', 'ilike', $like)->orWhere('recipient_name', 'ilike', $like)->orWhere('subject', 'ilike', $like);
            });
        }
        $logs = $q->orderBy('id', 'desc')->paginate(self::PER_PAGE)->appends($filters);

        $templates = MessageTemplate::where('kind', MessageTemplate::KIND_SYSTEM)->orderBy('name')->pluck('name', 'key')->all();
        $statuses = MessageLog::STATUSES;
        return view('backoffice::MessageLog.index', compact('logs', 'filters', 'templates', 'statuses'));
    }

    public function show($id)
    {
        $log = MessageLog::find((int) $id);
        abort_unless($log, 404);
        $templates = MessageTemplate::where('kind', MessageTemplate::KIND_SYSTEM)->pluck('name', 'key')->all();
        return response()->json([
            'channel'        => $log->channel,
            'template'       => isset($templates[$log->template_key]) ? $templates[$log->template_key] : $log->template_key,
            'recipient_name' => $log->recipient_name,
            'recipient'      => $log->recipient,
            'cc'             => $log->cc,
            'subject'        => $log->subject,
            'body'           => $log->body,
            'status'         => $log->status,
            'status_label'   => isset(MessageLog::STATUSES[$log->status]) ? MessageLog::STATUSES[$log->status] : $log->status,
            'note'           => $log->note,
            'created'        => $log->created_at ? $log->created_at->format('d/m/Y H:i') : '',
        ]);
    }
}
