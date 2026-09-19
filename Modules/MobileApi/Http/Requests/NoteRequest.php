<?php

namespace Modules\MobileApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NoteRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'text' => 'required|string|max:2000',
            'quick_reason' => 'nullable|in:part_required,tenant_not_available,access_denied,not_our_scope',
        ];
    }
}
