<?php

namespace Modules\Masters\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\BackOffice\Entities\OnboardingChecklistItem;

/**
 * Master list of the tenant onboarding (move-in) checklist the ARE fills on
 * the phone. Items are never deleted (entries reference them) — deactivate.
 */
class OnboardingChecklistItemController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:onboarding_items_manage');
    }

    private function rules(): array
    {
        return [
            'group_name' => 'required|string|max:100',
            'title' => 'required|string|max:200',
            'input_type' => 'required|in:' . implode(',', OnboardingChecklistItem::INPUT_TYPES),
            'required' => 'nullable|boolean',
            'needs_photo' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function index()
    {
        $items = OnboardingChecklistItem::orderBy('sort_order')->orderBy('id')->get();
        return view('masters::OnboardingItems.index', compact('items'));
    }

    public function create()
    {
        return view('masters::OnboardingItems.form');
    }

    public function store(Request $request)
    {
        $data = $this->validate($request, $this->rules());
        $item = OnboardingChecklistItem::create($this->normalise($data, true));

        activity('Add Onboarding Checklist Item')->performedOn($item)->causedBy(\Auth::user()->id)->withProperties($item)
            ->log('Created Name :' . \Auth::user()->username);
        session()->flash('success', 'Checklist item created successfully');
        return redirect()->route('onboarding-items.index');
    }

    public function edit(OnboardingChecklistItem $onboardingItem)
    {
        return view('masters::OnboardingItems.form', ['item' => $onboardingItem]);
    }

    public function update(Request $request, OnboardingChecklistItem $onboardingItem)
    {
        $data = $this->validate($request, $this->rules());
        $onboardingItem->update($this->normalise($data, false));

        activity('Edit Onboarding Checklist Item')->performedOn($onboardingItem)->causedBy(\Auth::user()->id)->withProperties($onboardingItem)
            ->log('Updated Name :' . \Auth::user()->username);
        session()->flash('success', 'Checklist item updated successfully');
        return redirect()->route('onboarding-items.index');
    }

    /** Toggle active instead of deleting — entries reference the item. */
    public function destroy(OnboardingChecklistItem $onboardingItem)
    {
        $onboardingItem->update(['is_active' => !$onboardingItem->is_active]);
        session()->flash('success', 'Checklist item ' . ($onboardingItem->is_active ? 'activated' : 'deactivated'));
        return redirect()->route('onboarding-items.index');
    }

    /** Drag-and-drop order from the list screen: ids in the new order. */
    public function reorder(Request $request)
    {
        $ids = (array) $request->input('ids', []);
        foreach ($ids as $pos => $id) {
            OnboardingChecklistItem::where('id', (int) $id)->update(['sort_order' => ($pos + 1) * 10]);
        }
        return response()->json(['ok' => true]);
    }

    /** Unchecked checkboxes are absent from the request. */
    private function normalise(array $data, bool $creating): array
    {
        $data['required'] = (bool) ($data['required'] ?? false);
        $data['needs_photo'] = (bool) ($data['needs_photo'] ?? false);
        $data['is_active'] = $creating ? (bool) ($data['is_active'] ?? true) : (bool) ($data['is_active'] ?? false);
        $data['sort_order'] = (int) ($data['sort_order'] ?? ((OnboardingChecklistItem::max('sort_order') ?? 0) + 10));
        return $data;
    }
}
