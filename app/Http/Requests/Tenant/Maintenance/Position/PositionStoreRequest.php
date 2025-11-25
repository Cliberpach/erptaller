<?php

namespace App\Http\Requests\Tenant\Maintenance\Position;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

class PositionStoreRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:160',
                Rule::unique('positions', 'name')->where(function ($query) {
                    return $query->where('status', '<>', 'ANULADO');
                }),
            ],
        ];
    }

    public function messages()
    {
        return [
            'name.required'               =>  'El campo nombre es obligatorio.',
            'name.string'                 =>  'El campo nombre debe ser una cadena de texto.',
            'name.max'                    =>  'El campo nombre no puede tener más de 150 caracteres.',
            'name.unique'                 =>  'El nombre ya está en uso, por favor elige otro.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
