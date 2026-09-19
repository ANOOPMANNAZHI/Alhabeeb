<?php

namespace Modules\MobileApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItemRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'inventory_id' => 'required|integer|exists:inventories,id',
            'quantity' => 'required|numeric|min:0.01',
        ];
    }
}
