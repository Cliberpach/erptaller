<?php

namespace App\Http\Requests\Landlord\Model;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

class ModelUpdateRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'brand_id_edit' => [
                'required',
                'integer',
                Rule::exists('landlord.brandsv', 'id')->where(function ($query) {
                    $query->where('status', 'ACTIVE');
                }),
            ],
            'description_edit' => [
                'required',
                'string',
                'max:191',
                Rule::unique('landlord.models', 'description')
                    ->ignore($this->route('id')) // Ignora el registro actual
                    ->where(function ($query) {
                        return $query->where('status', 'ACTIVE');
                    }),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'brand_id.required' => 'Debe seleccionar una marca.',
            'brand_id.integer'  => 'El identificador de marca no es válido.',
            'brand_id.exists'   => 'La marca seleccionada no existe o no está activa.',

            'description_edit.required' => 'El campo "descripción" es obligatorio.',
            'description_edit.string'   => 'El campo "descripción" debe ser una cadena de texto.',
            'description_edit.max'      => 'El campo "descripción" no debe exceder los 191 caracteres.',
            'description_edit.unique'   => 'Ya existe un color con esta descripción en estado ACTIVO.',

        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
