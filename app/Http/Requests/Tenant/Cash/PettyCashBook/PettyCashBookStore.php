<?php

namespace App\Http\Requests\Tenant\Cash\PettyCashBook;

use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class PettyCashBookStore extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalizamos valores antes de validar.
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'initial_amount' => $this->initial_amount ?? 0,
        ]);
    }

    public function rules(): array
    {
        return [
            'cash_available_id' => [
                'required',
                'integer',
                'exists:petty_cashes,id',
                'exists:petty_cashes,id,status,CERRADO'
            ],

            'shift' => [
                'required',
                'integer',
                'exists:shifts,id'
            ],

            'initial_amount' => [
                'required',
                'numeric',
                'min:0'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'cash_available_id.required' => 'La caja es obligatoria.',
            'cash_available_id.integer' => 'La caja seleccionada no es válida.',
            'cash_available_id.exists' => 'La caja seleccionada no existe o no está disponible.',

            'shift.required' => 'El turno es obligatorio.',
            'shift.integer' => 'El turno seleccionado no es válido.',
            'shift.exists' => 'El turno seleccionado no existe.',

            'initial_amount.required' => 'El monto inicial es obligatorio.',
            'initial_amount.numeric' => 'El monto inicial debe ser numérico.',
            'initial_amount.min' => 'El monto inicial debe ser mayor o igual a 0.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
