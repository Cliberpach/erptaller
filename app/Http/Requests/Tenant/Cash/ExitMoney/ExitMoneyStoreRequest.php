<?php

namespace App\Http\Requests\Tenant\Cash\ExitMoney;

use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class ExitMoneyStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'proof_payment'    => 'required',
            'number'           => 'required',
            'date'             => 'required|date',
            'reason'           => 'required',
            'supplier_id'      => 'required|exists:suppliers,id',
            'description.*'    => 'required|string',
            'total.*'          => 'required|numeric|min:0',

            'payment_method_id' => [
                'required',
                Rule::exists('payment_methods', 'id')->where('estado', 'ACTIVO'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'proof_payment.required'   => 'El tipo de comprobante es obligatorio.',
            'number.required'          => 'El número es obligatorio.',
            'date.required'            => 'La fecha de emisión es obligatoria.',
            'date.date'                => 'La fecha debe tener un formato válido.',
            'reason.required'          => 'La razón es obligatoria.',
            'supplier_id.required'     => 'Debe seleccionar un proveedor.',
            'supplier_id.exists'       => 'El proveedor seleccionado no es válido.',

            'description.*.required'   => 'La descripción es obligatoria.',
            'description.*.string'     => 'La descripción debe ser un texto.',

            'total.*.required'         => 'El total es obligatorio.',
            'total.*.numeric'          => 'El total debe ser un número válido.',
            'total.*.min'              => 'El total debe ser un valor positivo.',

            'payment_method_id.required' => 'Debe seleccionar un método de pago.',
            'payment_method_id.exists'   => 'El método de pago no es válido o no está activo.',

        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
