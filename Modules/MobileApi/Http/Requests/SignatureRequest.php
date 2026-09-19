<?php

namespace Modules\MobileApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SignatureRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'signature' => 'required_without:skipped|nullable|string|starts_with:data:image/png;base64,',
            'skipped' => 'nullable|boolean',
            'tenant_remark' => 'nullable|string|max:500',
        ];
    }
}
