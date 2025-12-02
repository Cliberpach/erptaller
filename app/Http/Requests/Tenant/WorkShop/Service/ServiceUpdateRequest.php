<?php

namespace App\Http\Requests\Tenant\WorkShop\Service;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

class ServiceUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_edit' => [
                'required',
                'string',
                'max:500',
                Rule::unique('services', 'name')
                    ->where('status', 'ACTIVE')
                    ->ignore($this->route('id'))
            ],

            'price_edit' => [
                'required',
                'numeric',
                'regex:/^\d{1,9}(\.\d{1,6})?$/'
            ],

            'description_edit' => ['nullable', 'string', 'max:300'],
        ];
    }


    public function messages(): array
    {
        return [
            'name_edit.required' => 'El nombre del servicio es obligatorio.',
            'name_edit.max' => 'El nombre no debe exceder los 160 caracteres.',
            'name_edit.unique' => 'El nombre del servicio ya está en uso por otro registro activo.',

            'price_edit.required' => 'El precio es obligatorio.',
            'price_edit.numeric' => 'El precio debe ser un valor numérico.',
            'price_edit.regex' => 'El precio debe tener un formato válido (máx. 9 enteros y 6 decimales).',

            'description_edit.max' => 'La descripción no debe exceder los 300 caracteres.'
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        return [
            'name'        => $data['name_edit'],
            'price'       => $data['price_edit'],
            'description' => $data['description_edit'] ?? null,
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
