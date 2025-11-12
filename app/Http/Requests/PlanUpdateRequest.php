<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlanUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description' => 'required',
            'number_fields' => 'required|numeric',
            'price' => 'required|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'description.required' => 'La descripción del plan es requerido',
            'number_fields.required' => 'El precio del plan es requerido',
            'number_fields.numeric' => 'El precio debe contener solo números ',
            'price.required' => 'El precio del plan es requerido',
            'price.numeric' => 'El precio debe contener solo números ',
        ];
    }
}
