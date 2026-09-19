<?php

namespace Modules\MobileApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'username' => 'required|string', 'password' => 'required|string',
            'device_uid' => 'required|string|max:191', 'device_name' => 'nullable|string|max:100',
            'platform' => 'nullable|in:android,ios', 'app_version' => 'nullable|string|max:20',
        ];
    }
}
