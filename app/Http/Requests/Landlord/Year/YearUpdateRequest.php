<?php

namespace App\Http\Requests\Landlord\Year;

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
            'description_edit' => [
                'required',
                'string',
                'max:191',
                Rule::unique('landlord.years', 'description')
                    ->ignore($this->route('id')) // Ignora el registro actual al editar
                    ->where(fn($q) =>
                        $q->where('status', 'ACTIVE')
                    ),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'description_edit.required' => 'El campo "descripción" es obligatorio.',
            'description_edit.string'   => 'El campo "descripción" debe ser una cadena de texto.',
            'description_edit.max'      => 'El campo "descripción" no debe exceder los 191 caracteres.',
            'description_edit.unique'   => 'Ya existe un año con esta descripción en estado ACTIVO.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
