<?php

namespace Modules\MobileApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InspectionPhotoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'photo' => 'required|image|mimes:jpeg,png,webp|max:10240',
            'type' => 'required|in:inspection,meter_electricity,meter_water,key',
        ];
    }
}
