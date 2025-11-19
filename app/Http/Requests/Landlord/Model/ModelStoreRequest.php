<?php

namespace App\Http\Requests\Landlord\Model;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\DB;

class ModelStoreRequest extends FormRequest
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
            'brand_id' => [
                'required',
                'integer',
                Rule::exists('landlord.brandsv', 'id')->where(function ($query) {
                    $query->where('status', 'ACTIVE');
                }),
            ],
            'description' => [
                'required',
                'string',
                'max:191',
                Rule::unique('landlord.models')->where(function ($query) {
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
            'brand_id.required' => 'Debe seleccionar una marca.',
            'brand_id.integer'  => 'El identificador de marca no es válido.',
            'brand_id.exists'   => 'La marca seleccionada no existe o no está activa.',

            'description.required' => 'El campo "descripción" es obligatorio.',
            'description.string'   => 'El campo "descripción" debe ser una cadena de texto.',
            'description.max'      => 'El campo "descripción" no debe exceder los 191 caracteres.',
            'description.unique'   => 'Ya existe un modelo con esta descripción en estado ACTIVO.',
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
