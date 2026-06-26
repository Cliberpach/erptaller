<?php

namespace App\Http\Requests\Tenant\Maintenance\Sede;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

class SedeUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        $id = $this->route('id');

        return [
            'nombre_edit'    => ['required', 'string', 'max:120', Rule::unique('sedes', 'nombre')->ignore($id)],
            'codigo_edit'    => ['required', 'string', 'max:20', Rule::unique('sedes', 'codigo')->ignore($id)],
            'direccion_edit' => ['nullable', 'string', 'max:200'],
            'telefono_edit'  => ['nullable', 'string', 'max:20'],
            'ubigeo_edit'    => ['nullable', 'string', 'max:6'],
        ];
    }

    public function messages()
    {
        return [
            'nombre_edit.required' => 'El campo nombre es obligatorio.',
            'nombre_edit.max'      => 'El nombre no puede tener más de 120 caracteres.',
            'nombre_edit.unique'   => 'Ya existe una sede con ese nombre.',
            'codigo_edit.required' => 'El campo código es obligatorio.',
            'codigo_edit.max'      => 'El código no puede tener más de 20 caracteres.',
            'codigo_edit.unique'   => 'Ya existe una sede con ese código.',
            'ubigeo_edit.max'      => 'El ubigeo no puede tener más de 6 caracteres.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
