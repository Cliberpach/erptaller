<?php

namespace App\Http\Requests\Tenant\Cash\Cash;

use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class CashStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $sedeId = $this->input('sede_id');

        return [
            // name único POR SEDE (no global): "CAJA PRINCIPAL" puede repetirse entre sedes.
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('petty_cashes', 'name')->where(
                    fn ($q) => $q->where('sede_id', $sedeId)->where('status', '<>', 'ANULADO')
                ),
            ],
            'sede_id' => ['required', Rule::exists('sedes', 'id')->where('status', 'ACTIVO')],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'    => 'El campo nombre es obligatorio.',
            'name.unique'      => 'Ya existe una caja con ese nombre en esta sede.',
            'name.max'         => 'El nombre no debe exceder los 255 caracteres.',
            'sede_id.required' => 'Debe seleccionar una sede.',
            'sede_id.exists'   => 'La sede seleccionada no es válida.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
