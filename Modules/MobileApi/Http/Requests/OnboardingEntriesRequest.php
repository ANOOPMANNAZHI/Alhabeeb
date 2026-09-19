<?php

namespace Modules\MobileApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OnboardingEntriesRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'entries' => 'required|array|min:1',
            'entries.*.item_id' => 'required|integer|exists:onboarding_checklist_items,id',
            'entries.*.value' => 'nullable|string|max:1000',
            'entries.*.remark' => 'nullable|string|max:1000',
            'entries.*.done' => 'nullable|boolean',
            'entries.*.not_applicable' => 'nullable|boolean',
        ];
    }
}
