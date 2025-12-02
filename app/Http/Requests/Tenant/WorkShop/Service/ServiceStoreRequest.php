<?php

namespace App\Http\Requests\Tenant\WorkShop\Service;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

class ServiceStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->replace(
            collect($this->all())->mapWithKeys(function ($value, $key) {

                if (str_ends_with($key, '_mdlservice')) {
                    $newKey = str_replace('_mdlservice', '', $key);
                    return [$newKey => $value];
                }

                return [$key => $value];
            })->toArray()
        );
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:500',
                Rule::unique('services', 'name')
                    ->where('status', 'ACTIVE'),
            ],

            'price' => [
                'required',
                'numeric',
                'regex:/^\d{1,9}(\.\d{1,6})?$/'
            ],

            'description' => ['nullable', 'string', 'max:300'],
        ];
    }


    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del servicio es obligatorio.',
            'name.max' => 'El nombre no debe exceder los 160 caracteres.',
            'name.unique' => 'El nombre del servicio ya existe y está activo.',

            'price.required' => 'El precio es obligatorio.',
            'price.numeric' => 'El precio debe ser un valor numérico.',
            'price.regex' => 'El precio debe tener un formato válido (máx. 9 enteros y 6 decimales).',

            'description.max' => 'La descripción no debe exceder los 300 caracteres.'
        ];
    }


    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
