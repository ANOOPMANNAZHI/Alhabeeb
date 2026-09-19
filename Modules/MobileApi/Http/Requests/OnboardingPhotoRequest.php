<?php

namespace Modules\MobileApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OnboardingPhotoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'photo' => 'required|image|mimes:jpeg,png,webp|max:10240',
            'item_id' => 'required|integer|exists:onboarding_checklist_items,id',
        ];
    }
}
