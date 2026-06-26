<?php

namespace App\Http\Requests\Tenant\Maintenance\Sede;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;

class SerieUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        // serie ÚNICA en TODO el tenant (no por sede ni por tipo): dos series iguales =
        // comprobantes duplicados ante SUNAT. Se excluye la fila actual (route {serie} = id).
        // No se valida current_number: es el correlativo, lo maneja la emisión (Capa C).
        return [
            'serie'        => [
                'required', 'string', 'max:10',
                Rule::unique('document_serializations', 'serie')->ignore($this->route('serie')),
            ],
            'start_number' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages()
    {
        return [
            'serie.required'        => 'La serie es obligatoria.',
            'serie.max'             => 'La serie no puede tener más de 10 caracteres.',
            'serie.unique'          => 'Esa serie ya está en uso (debe ser única en toda la empresa).',
            'start_number.required' => 'El número inicial es obligatorio.',
            'start_number.integer'  => 'El número inicial debe ser un entero.',
            'start_number.min'      => 'El número inicial debe ser 1 o mayor.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
