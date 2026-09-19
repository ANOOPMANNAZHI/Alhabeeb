<?php

namespace Modules\MobileApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InspectionSubmitRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'signature' => 'required|string|starts_with:data:image/png;base64,',
            'tenant_remark' => 'nullable|string|max:1000',
        ];
    }
}
