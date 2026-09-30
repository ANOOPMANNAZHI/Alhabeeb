<?php

namespace Modules\BackOffice\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\BackOffice\Entities\MessageTemplate;
use Modules\BackOffice\Services\MessagePlaceholders;
use Modules\BackOffice\Services\SystemTemplateDefaults;
use Modules\BackOffice\Services\MessageTemplateRenderer as Renderer;

/** Mail -> Templates: editable email + SMS templates (project A of the messaging roadmap). */
class MessageTemplateController extends Controller
{
    const PER_PAGE = 25;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view_message_templates|manage_message_templates')->only(['index', 'preview']);
        $this->middleware('permission:manage_message_templates')->except(['index', 'preview']);
    }

    public function index(Request $request)
    {
        $filters = [
            'q'        => trim((string) $request->input('q')),
            'kind'     => in_array($request->input('kind'), ['system', 'bulk'], true) ? $request->input('kind') : '',
            'audience' => array_key_exists((string) $request->input('audience'), MessagePlaceholders::AUDIENCES) ? $request->input('audience') : '',
        ];
        $q = MessageTemplate::query();
        if ($filters['q'] !== '') {
            $like = '%' . $filters['q'] . '%';
            $q->where(function ($w) use ($like) { $w->where('name', 'ilike', $like)->orWhere('email_subject', 'ilike', $like); });
        }
        if ($filters['kind'] !== '') { $q->where('kind', $filters['kind']); }
        if ($filters['audience'] !== '') { $q->where('audience', $filters['audience']); }
        $templates = $q->orderBy('kind', 'desc')->orderBy('name')->paginate(self::PER_PAGE)->appends($filters);
        $canManage = \Auth::user()->can('manage_message_templates');
        $audiences = MessagePlaceholders::AUDIENCES;
        return view('backoffice::MessageTemplate.index', compact('templates', 'filters', 'canManage', 'audiences'));
    }

    public function create()
    {
        return $this->form(null);
    }

    public function edit(MessageTemplate $messageTemplate)
    {
        return $this->form($messageTemplate);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, null);
        $data['kind'] = MessageTemplate::KIND_BULK;
        $data['key'] = null;
        $data['created_by'] = \Auth::user()->id;
        MessageTemplate::create($data);
        return redirect()->route('message-templates.index')->with('success', 'Template "' . $data['name'] . '" created.');
    }

    public function update(Request $request, MessageTemplate $messageTemplate)
    {
        $data = $this->validated($request, $messageTemplate);
        if ($messageTemplate->isSystem()) {
            unset($data['audience']); // fixed for system templates; key is never taken from the request
        }
        $data['updated_by'] = \Auth::user()->id;
        $messageTemplate->update($data);
        return redirect()->route('message-templates.index')->with('success', 'Template "' . $messageTemplate->name . '" saved.');
    }

    public function destroy(MessageTemplate $messageTemplate)
    {
        if ($messageTemplate->isSystem()) {
            return redirect()->route('message-templates.index')->with('error', 'System templates cannot be deleted; switch them off instead.');
        }
        $messageTemplate->delete();
        return redirect()->route('message-templates.index')->with('success', 'Template "' . $messageTemplate->name . '" deleted.');
    }

    /** System templates: put back the default subject, body and SMS text (switches are kept). */
    public function reset(MessageTemplate $messageTemplate)
    {
        abort_unless($messageTemplate->isSystem(), 404);
        $d = SystemTemplateDefaults::get($messageTemplate->key);
        if (!$d) {
            return redirect()->route('message-templates.edit', $messageTemplate->id)->with('error', 'No default wording exists for this template.');
        }
        $messageTemplate->update([
            'email_subject' => $d['email_subject'], 'email_body' => $d['email_body'], 'sms_body' => $d['sms_body'],
            'updated_by' => \Auth::user()->id,
        ]);
        return redirect()->route('message-templates.edit', $messageTemplate->id)->with('success', 'Default wording restored.');
    }

    /** JSON preview with sample values: from a saved template (id) or the editor's current content. */
    public function preview(Request $request)
    {
        $template = $request->filled('id') ? MessageTemplate::find((int) $request->input('id')) : null;
        $source = $template ?: (object) [
            'email_subject' => (string) $request->input('email_subject'),
            'email_body'    => Renderer::cleanHtml((string) $request->input('email_body')),
            'sms_body'      => (string) $request->input('sms_body'),
            'audience'      => (string) $request->input('audience', 'any'),
            'key'           => $request->input('key'),
        ];
        $samples = MessagePlaceholders::samples($source->audience, $source->key);
        $out = Renderer::render($source, $samples, MessagePlaceholders::blockSamples($source->audience, $source->key));
        $out['sms_parts'] = Renderer::smsParts($out['sms']);
        return response()->json($out);
    }

    private function form($template)
    {
        $audience = $template ? $template->audience : 'tenant';
        $key = $template ? $template->key : null;
        $placeholders = MessagePlaceholders::forTemplate($audience, $key);
        $audiences = MessagePlaceholders::AUDIENCES;
        $allPlaceholders = [];
        foreach (array_keys($audiences) as $a) {
            $allPlaceholders[$a] = MessagePlaceholders::forTemplate($a, $key);
        }
        return view('backoffice::MessageTemplate.form', compact('template', 'placeholders', 'audiences', 'allPlaceholders'));
    }

    private function validated(Request $request, $template)
    {
        $this->validate($request, [
            'name'          => 'required|max:150',
            'audience'      => 'required|in:' . implode(',', array_keys(MessagePlaceholders::AUDIENCES)),
            'email_subject' => 'nullable|string|max:255',
            'email_body'    => 'nullable|string',
            'sms_body'      => 'nullable|string|max:1000',
        ]);
        // The editor posts "<p>&nbsp;</p>" and similar when empty: treat those as no body.
        $body = Renderer::isBlankHtml($request->input('email_body')) ? null : Renderer::cleanHtml($request->input('email_body'));
        if ($body !== null && Renderer::isBlankHtml($body)) {
            $body = null;
        }
        $data = [
            'name'          => trim($request->input('name')),
            'audience'      => $request->input('audience'),
            'email_subject' => $request->filled('email_subject') ? trim($request->input('email_subject')) : null,
            'email_body'    => $body,
            'sms_body'      => $request->filled('sms_body') ? $request->input('sms_body') : null,
            'is_active'     => (bool) $request->input('is_active'),
        ];
        if ($data['email_subject'] !== null && $data['email_body'] === null) {
            throw ValidationException::withMessages(['email_body' => 'Write the email body, or clear the subject for an SMS-only template.']);
        }
        if ($data['email_body'] !== null && $data['email_subject'] === null) {
            throw ValidationException::withMessages(['email_subject' => 'Write the email subject, or clear the body for an SMS-only template.']);
        }
        if ($template && $template->isSystem()) {
            $data['sms_enabled'] = (bool) $request->input('sms_enabled');
        }
        if ($data['email_subject'] === null && $data['sms_body'] === null) {
            throw ValidationException::withMessages(['channels' => 'Fill in the email (subject and body) or the SMS text, or both.']);
        }
        return $data;
    }
}
