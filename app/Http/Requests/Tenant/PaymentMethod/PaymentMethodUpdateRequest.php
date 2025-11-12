<?php

namespace App\Http\Requests\Tenant\PaymentMethod;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class PaymentMethodUpdateRequest extends FormRequest
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
            'descripcion_edit' => [
                'required',
                'string',
                'max:150',
                Rule::unique('payment_methods', 'description')
                    ->ignore($this->route('id')) 
                    ->where(function ($query) {
                        return $query->where('estado', '<>', 'ANULADO');
                    }),
            ],
        ];
    }

    public function messages()
    {
        return [
            'descripcion_edit.required'               =>  'El campo nombre es obligatorio.',
            'descripcion_edit.string'                 =>  'El campo nombre debe ser una cadena de texto.',
            'descripcion_edit.max'                    =>  'El campo nombre no puede tener más de 150 caracteres.',
            'descripcion_edit.unique'                 =>  'El nombre ya está en uso, por favor elige otro.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'errors' => $validator->errors()
        ], 422));
    }

}
