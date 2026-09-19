<?php

namespace Modules\MobileApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class InspectionDraftRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'lines' => 'present|array',
            'lines.*.work_id' => 'nullable|integer|exists:works,id',
            'lines.*.sub_work_id' => 'nullable|integer|exists:sub_work,id',
            'lines.*.other_work' => 'nullable|string|max:200',
            'lines.*.quantity' => 'required|numeric|min:0',
            'lines.*.amount' => 'required|numeric|min:0',
            'electricity.acc_no' => 'nullable|string|max:50',
            'electricity.close_reading' => 'nullable|string|max:50',
            'electricity.amount' => 'nullable|numeric|min:0',
            'water.acc_no' => 'nullable|string|max:50',
            'water.close_reading' => 'nullable|string|max:50',
            'water.amount' => 'nullable|numeric|min:0',
            'main_key_status' => 'required|in:0,1',
            'maintenance_due' => 'nullable|numeric|min:0',
            'notes' => 'required|string|max:2000',
            'remark' => 'nullable|string|max:1000',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            foreach ((array) $this->input('lines', []) as $i => $line) {
                if (empty($line['sub_work_id']) && empty($line['other_work'])) {
                    $v->errors()->add("lines.$i", 'Each line needs a sub_work_id or an other_work description.');
                }
            }
        });
    }
}
