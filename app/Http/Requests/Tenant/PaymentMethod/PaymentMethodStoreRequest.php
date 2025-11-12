<?php

namespace App\Http\Requests\Tenant\PaymentMethod;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

class PaymentMethodStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
           'descripcion' => [
                'required',
                'string',
                'max:160',
                Rule::unique('payment_methods', 'description')->where(function ($query) {
                    return $query->where('estado', '<>', 'ANULADO');
                }),
            ],
        ];
    }

    public function messages()
    {
        return [
            'descripcion.required'               =>  'El campo nombre es obligatorio.',
            'descripcion.string'                 =>  'El campo nombre debe ser una cadena de texto.',
            'descripcion.max'                    =>  'El campo nombre no puede tener más de 150 caracteres.',
            'descripcion.unique'                 =>  'El nombre ya está en uso, por favor elige otro.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'errors' => $validator->errors()
        ], 422));
    }

}
