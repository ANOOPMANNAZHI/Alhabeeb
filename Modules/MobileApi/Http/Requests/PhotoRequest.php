<?php

namespace Modules\MobileApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PhotoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'photo' => 'required|image|mimes:jpeg,png,webp|max:10240',
            'stage' => 'required|in:before,during,after',
        ];
    }
}
