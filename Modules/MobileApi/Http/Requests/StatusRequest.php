<?php

namespace Modules\MobileApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StatusRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'status' => 'required|in:attended,completed,cannot_complete',
            'reason' => 'required_if:status,cannot_complete|nullable|string|max:500',
        ];
    }
}
