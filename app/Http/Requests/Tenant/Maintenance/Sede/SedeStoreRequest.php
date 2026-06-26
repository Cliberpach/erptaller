<?php

namespace App\Http\Requests\Tenant\Maintenance\Sede;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

class SedeStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'nombre'    => ['required', 'string', 'max:120', Rule::unique('sedes', 'nombre')],
            'codigo'    => ['required', 'string', 'max:20', Rule::unique('sedes', 'codigo')],
            'direccion' => ['nullable', 'string', 'max:200'],
            'telefono'  => ['nullable', 'string', 'max:20'],
            'ubigeo'    => ['nullable', 'string', 'max:6'],
        ];
    }

    public function messages()
    {
        return [
            'nombre.required' => 'El campo nombre es obligatorio.',
            'nombre.max'      => 'El nombre no puede tener más de 120 caracteres.',
            'nombre.unique'   => 'Ya existe una sede con ese nombre.',
            'codigo.required' => 'El campo código es obligatorio.',
            'codigo.max'      => 'El código no puede tener más de 20 caracteres.',
            'codigo.unique'   => 'Ya existe una sede con ese código.',
            'ubigeo.max'      => 'El ubigeo no puede tener más de 6 caracteres.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
