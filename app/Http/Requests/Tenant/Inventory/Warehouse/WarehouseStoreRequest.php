<?php

namespace App\Http\Requests\Tenant\Inventory\Warehouse;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;

class WarehouseStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'descripcion' => ['required', 'string', 'max:120'],
        ];
    }

    public function messages()
    {
        return [
            'descripcion.required' => 'El nombre del almacén es obligatorio.',
            'descripcion.max'      => 'El nombre no puede tener más de 120 caracteres.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
