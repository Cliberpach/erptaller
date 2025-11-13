<?php

namespace App\Http\Requests\Tenant\WorkShop\Year;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

class YearUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            
            'model_id_edit' => [
                'required',
                'integer',
                Rule::exists('models', 'id')->where(fn($q) =>
                    $q->where('status', 'ACTIVE')
                ),
            ],

            'description_edit' => [
                'required',
                'string',
                'max:191',
                Rule::unique('model_years', 'description')
                    ->ignore($this->route('id')) // Ignora el registro actual al editar
                    ->where(fn($q) =>
                        $q->where('model_id', $this->input('model_id_edit'))
                          ->where('status', 'ACTIVE')
                    ),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'brand_id_edit.required' => 'Debe seleccionar una marca.',
            'brand_id_edit.integer'  => 'El identificador de marca no es válido.',
            'brand_id_edit.exists'   => 'La marca seleccionada no existe o no está activa.',

            'model_id_edit.required' => 'Debe seleccionar un modelo.',
            'model_id_edit.integer'  => 'El identificador de modelo no es válido.',
            'model_id_edit.exists'   => 'El modelo seleccionado no existe o no está activo.',

            'description_edit.required' => 'El campo "descripción" es obligatorio.',
            'description_edit.string'   => 'El campo "descripción" debe ser una cadena de texto.',
            'description_edit.max'      => 'El campo "descripción" no debe exceder los 191 caracteres.',
            'description_edit.unique'   => 'Ya existe un año con esta descripción para el modelo seleccionado en estado ACTIVO.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
