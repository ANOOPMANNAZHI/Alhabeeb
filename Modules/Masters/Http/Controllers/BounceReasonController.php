<?php

namespace Modules\Masters\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Masters\Entities\PdcBounceReason;

/**
 * Masters -> Bounce Reason: the reasons offered when a PDC is bounced, and the
 * "bank sheet words" the PDC bank-sheet upload uses to pre-select one.
 * No delete (PDCs keep the code); switch a reason off instead.
 */
class BounceReasonController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view_bounce_reason', ['only' => ['index']]);
        $this->middleware('permission:add_bounce_reason', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit_bounce_reason', ['only' => ['edit', 'update']]);
    }

    public function index()
    {
        $reasons = PdcBounceReason::orderBy('sort_order')->orderBy('id')->get();
        $usage = \DB::table('pdc')->where('pdc_cancel_reason', 1)->whereNotNull('pdc_bounce_reason')
            ->select('pdc_bounce_reason', \DB::raw('count(*) as c'))->groupBy('pdc_bounce_reason')->pluck('c', 'pdc_bounce_reason')->all();
        return view('masters::BounceReason.index', compact('reasons', 'usage'));
    }

    public function create()
    {
        $reason = null;
        return view('masters::BounceReason.form', compact('reason'));
    }

    public function edit(PdcBounceReason $bounceReason)
    {
        $reason = $bounceReason;
        return view('masters::BounceReason.form', compact('reason'));
    }

    public function store(Request $request)
    {
        $reason = PdcBounceReason::create($this->validated($request, null) + ['created_by' => \Auth::user()->id]);
        $this->keepOneFallback($reason);
        PdcBounceReason::flushCache();
        return redirect()->route('bounce-reason.index')->with('success', 'Bounce reason "' . $reason->name . '" added.');
    }

    public function update(Request $request, PdcBounceReason $bounceReason)
    {
        $bounceReason->update($this->validated($request, $bounceReason) + ['updated_by' => \Auth::user()->id]);
        $this->keepOneFallback($bounceReason);
        PdcBounceReason::flushCache();
        return redirect()->route('bounce-reason.index')->with('success', 'Bounce reason "' . $bounceReason->name . '" saved.');
    }

    private function validated(Request $request, $reason)
    {
        $this->validate($request, [
            'name'          => ['required', 'max:100', Rule::unique('pdc_bounce_reasons', 'name')->ignore($reason ? $reason->id : null)],
            'bank_keywords' => 'nullable|max:500',
            'sort_order'    => 'nullable|integer|min:0|max:9999',
        ], ['name.unique' => 'A bounce reason with this name already exists.']);
        $active = (bool) $request->input('is_active');
        return [
            'name'          => trim($request->input('name')),
            'bank_keywords' => implode(', ', PdcBounceReason::splitKeywords($request->input('bank_keywords'))) ?: null,
            'sort_order'    => (int) $request->input('sort_order', 0),
            'is_active'     => $active,
            // a switched-off reason can never be the automatic choice
            'is_fallback'   => $active && (bool) $request->input('is_fallback'),
        ];
    }

    /** Only one reason is used when the bank's text matches nothing. */
    private function keepOneFallback(PdcBounceReason $reason)
    {
        if ($reason->is_fallback) {
            PdcBounceReason::where('id', '<>', $reason->id)->where('is_fallback', true)->update(['is_fallback' => false]);
        }
    }
}
