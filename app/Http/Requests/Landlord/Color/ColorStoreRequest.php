<?php

namespace App\Http\Requests\Landlord\Color;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

class ColorStoreRequest extends FormRequest
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
            'description' => [
                'required',
                'string',
                'max:191',
                Rule::unique('landlord.colors')->where(function ($query) {
                    return $query->where('status', 'ACTIVE');
                }),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'description.required' => 'El campo "descripción" es obligatorio.',
            'description.string'   => 'El campo "descripción" debe ser una cadena de texto.',
            'description.max'      => 'El campo "descripción" no debe exceder los 191 caracteres.',
            'description.unique'   => 'Ya existe un color con esta descripción en estado ACTIVO.'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
