<?php

namespace App\Http\Requests\Landlord\Year;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\DB;

class YearStoreRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación.
     */
    public function rules(): array
    {
        return [
            'description' => [
                'required',
                'string',
                'max:191',
                Rule::unique('landlord.years')->where(function ($query) {
                    return $query->where('status', 'ACTIVE');
                }),
            ],
        ];
    }

    /**
     * Mensajes personalizados.
     */
    public function messages(): array
    {
        return [
            'description.required' => 'El campo "descripción" es obligatorio.',
            'description.string'   => 'El campo "descripción" debe ser una cadena de texto.',
            'description.max'      => 'El campo "descripción" no debe exceder los 191 caracteres.',
            'description.unique'   => 'Ya existe un año con esta descripción en estado ACTIVO.',
        ];
    }

    /**
     * Manejo de validación fallida.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
