<?php

namespace Modules\BackOffice\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\BackOffice\Entities\BulkSend;
use Modules\BackOffice\Entities\MessageTemplate;
use Modules\BackOffice\Services\Bulk\BulkSender;
use Modules\BackOffice\Services\Bulk\BulkSendService;
use Modules\BackOffice\Services\Bulk\RecipientFinder;
use Modules\BackOffice\Services\MessagePlaceholders;
use Modules\BackOffice\Services\MessageTemplateRenderer;

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

    /** Request -> the input BulkSendService expects. */
    private function input(Request $request)
    {
        $audience = (string) $request->input('audience');
        $filters = [];
        if ($audience === 'staff') {
            $filters['roles'] = array_values(array_filter((array) $request->input('roles', [])));
        } else {
            $filters['locations'] = array_values(array_filter((array) $request->input('locations', [])));
            $filters['buildings'] = array_values(array_filter((array) $request->input('buildings', [])));
        }
        return [
            'template_id'   => $request->input('template_id') ? (int) $request->input('template_id') : null,
            'email_subject' => $request->input('email_subject'),
            'email_body'    => $request->input('email_body'),
            'sms_body'      => $request->input('sms_body'),
            'audience'      => $audience,
            'filters'       => $filters,
            'send_email'    => (bool) $request->input('send_email'),
            'send_sms'      => (bool) $request->input('send_sms'),
            'exclude'       => array_values((array) $request->input('exclude', [])),
            'add'           => array_values((array) $request->input('add', [])),
        ];
    }

    public function create()
    {
        $templates = MessageTemplate::where('kind', MessageTemplate::KIND_BULK)->where('is_active', true)->orderBy('name')
            ->get(['id', 'name', 'audience', 'email_subject', 'email_body', 'sms_body']);
        $locations = DB::table('locations')->where('locations_status', 1)->orderBy('locations_name')->get(['id', 'locations_name']);
        $buildings = DB::table('buildings')->where('building_status', 1)->orderBy('building_name')->get(['id', 'building_name', 'location_id']);
        $roles = DB::table('roles')->orderBy('name')->pluck('name');
        $placeholders = [];
        foreach (array_keys(MessagePlaceholders::AUDIENCES) as $a) {
            $placeholders[$a] = MessagePlaceholders::forTemplate($a);
        }
        return view('backoffice::BulkSend.create', compact('templates', 'locations', 'buildings', 'roles', 'placeholders'));
    }

    public function count(Request $request)
    {
        $in = $this->input($request);
        if (!isset(BulkSend::AUDIENCE_LABELS[$in['audience']])) {
            return response()->json(['total' => 0, 'email' => 0, 'mobile' => 0]);
        }
        return response()->json(RecipientFinder::count($in['audience'], $in['filters']));
    }

    /** Compose page: how many people each audience has (no filters). */
    public function audienceCounts()
    {
        $out = [];
        foreach (array_keys(BulkSend::AUDIENCE_LABELS) as $audience) {
            $out[$audience] = RecipientFinder::count($audience, [])['total'];
        }
        return response()->json($out);
    }

    /**
     * Compose page: people of an audience per location / building (tenants, landlords) or per role (staff).
     * A person counts once, for the building of the contract used for them.
     */
    public function placeCounts(Request $request)
    {
        $audience = (string) $request->input('audience');
        if ($audience === 'staff') {
            $ids = [];
            foreach (RecipientFinder::people('staff', []) as $p) {
                $ids[$p['person_id']] = true;
            }
            $rows = DB::table('model_has_roles')->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->where('model_has_roles.model_type', 'App\User')->get(['roles.name', 'model_has_roles.model_id']);
            $roles = [];
            foreach ($rows as $r) {
                if (isset($ids[(int) $r->model_id])) {
                    $roles[$r->name] = (isset($roles[$r->name]) ? $roles[$r->name] : 0) + 1;
                }
            }
            return response()->json(['roles' => (object) $roles]);
        }
        $locations = [];
        $buildings = [];
        if (isset(BulkSend::AUDIENCE_LABELS[$audience])) {
            $locationOf = DB::table('buildings')->pluck('location_id', 'id')->all();
            foreach (RecipientFinder::people($audience, []) as $p) {
                $b = $p['building_id'];
                if (!$b) {
                    continue;
                }
                $buildings[$b] = (isset($buildings[$b]) ? $buildings[$b] : 0) + 1;
                $l = isset($locationOf[$b]) ? (int) $locationOf[$b] : 0;
                if ($l) {
                    $locations[$l] = (isset($locations[$l]) ? $locations[$l] : 0) + 1;
                }
            }
        }
        return response()->json(['locations' => (object) $locations, 'buildings' => (object) $buildings]);
    }

    public function review(Request $request)
    {
        $in = $this->input($request);
        try {
            $texts = BulkSendService::checkMessage($in);
        } catch (ValidationException $e) {
            return redirect()->route('bulk-send.create')->withErrors($e->errors())->withInput();
        }
        $people = RecipientFinder::people($in['audience'], $in['filters']);
        $list = [];
        foreach ($people as $key => $p) {
            $list[] = ['key' => $key, 'name' => $p['name'], 'email' => $p['email'], 'mobile' => $p['mobile']];
        }
        $perMinute = (int) (prefixData('bulk_emails_per_minute') ? prefixData('bulk_emails_per_minute')->configuration_value : 25);
        $perMinute = $perMinute > 0 ? min(BulkSender::MAX_PER_MINUTE, $perMinute) : 25;
        // One-time token: a double click or a resubmitted page cannot start the same send twice.
        $token = Str::random(40);
        $tokens = (array) $request->session()->get('bulk_send_tokens', []);
        $tokens[] = $token;
        $request->session()->put('bulk_send_tokens', array_slice($tokens, -10));
        return view('backoffice::BulkSend.review', [
            'sendToken' => $token, 'in' => $in, 'texts' => $texts, 'list' => $list, 'perMinute' => $perMinute,
            'audienceLabel' => BulkSend::AUDIENCE_LABELS[$in['audience']],
            'template' => $texts['template_id'] ? MessageTemplate::find($texts['template_id']) : null,
        ]);
    }

    public function search(Request $request)
    {
        $audience = (string) $request->input('audience');
        if (!isset(BulkSend::AUDIENCE_LABELS[$audience])) {
            return response()->json([]);
        }
        return response()->json(RecipientFinder::search($audience, $request->input('q')));
    }

    public function preview(Request $request)
    {
        $in = $this->input($request);
        if (!isset(BulkSend::AUDIENCE_LABELS[$in['audience']])) {
            return response()->json(['subject' => null, 'html' => null, 'sms' => null]);
        }
        try {
            $texts = BulkSendService::texts($in, $in['audience']);
        } catch (ValidationException $e) {
            return response()->json(['error' => collect($e->errors())->flatten()->first()], 422);
        }
        $person = RecipientFinder::person($in['audience'], (string) $request->input('key'));
        $values = ($person ? $person['values'] : []) + ['today' => date('d/m/Y'), 'company_name' => 'Al Habib & Co. L.L.C'];
        $out = MessageTemplateRenderer::render($texts, $values);
        return response()->json(['subject' => $out['subject'], 'html' => $out['html'], 'sms' => $out['sms']]);
    }

    public function store(Request $request)
    {
        $token = (string) $request->input('send_token');
        $tokens = (array) $request->session()->get('bulk_send_tokens', []);
        $at = $token === '' ? false : array_search($token, $tokens, true);
        if ($at === false) {
            return redirect()->route('bulk-send.index')->with('error', 'This send was already started (or the page expired). Check the list below.');
        }
        unset($tokens[$at]);
        $request->session()->put('bulk_send_tokens', array_values($tokens));
        try {
            $send = BulkSendService::start($this->input($request), \Auth::id());
        } catch (ValidationException $e) {
            return redirect()->route('bulk-send.create')->withErrors($e->errors())->withInput();
        }
        return redirect()->route('bulk-send.index')->with('success', 'Send #' . $send->id . ' started: ' . $send->total . ' people.');
    }

    public function show(Request $request, BulkSend $bulkSend)
    {
        $status = $request->input('status');
        $q = $bulkSend->recipients()->orderBy('id');
        if ($status && in_array($status, ['pending', 'sent', 'failed', 'skipped', 'cancelled'], true)) {
            $q->where('status', $status);
        }
        $recipients = $q->paginate(self::PER_PAGE)->appends(['status' => $status]);
        $statusCounts = ['pending' => 0, 'sent' => 0, 'failed' => 0, 'skipped' => 0, 'cancelled' => 0];
        $grouped = DB::table('bulk_send_recipients')->where('bulk_send_id', $bulkSend->id)
            ->groupBy('status')->select('status', DB::raw('count(*) as n'))->pluck('n', 'status');
        foreach ($grouped as $st => $n) {
            if (isset($statusCounts[$st])) {
                $statusCounts[$st] = (int) $n;
            }
        }
        return view('backoffice::BulkSend.show', [
            'send' => $bulkSend, 'recipients' => $recipients, 'status' => $status, 'statusCounts' => $statusCounts,
        ]);
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
