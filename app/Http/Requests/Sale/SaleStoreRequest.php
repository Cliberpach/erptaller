<?php

namespace App\Http\Requests\Sale;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

class SaleStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type_sale' => [
                'required',
                Rule::exists('landlord.general_table_details', 'id')
                    ->where('status', 'ACTIVO')
            ],

            'customer_id' => [
                'required',
                Rule::exists('customers', 'id')
                    ->where('status', 'ACTIVO'),
            ],

            'payment_condition_id' => [
                'required',
                Rule::exists('payment_conditions', 'id')
                    ->where('status', 'ACTIVO'),
            ],

            'registration_date' => [
                'required',
                'date',
            ],

            'expiration_date' => [
                'required',
                'date',
                'after_or_equal:registration_date',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'type_sale.required' => 'El tipo de venta es obligatorio.',
            'type_sale.exists'   => 'El tipo de venta seleccionado no es válido.',

            'customer_id.required' => 'El cliente es obligatorio.',
            'customer_id.exists'   => 'El cliente seleccionado debe estar activo.',

            // ✅ MENSAJES CONDICIÓN DE PAGO
            'payment_condition_id.required' => 'La condición de pago es obligatoria.',
            'payment_condition_id.exists'   =>
                'La condición de pago seleccionada no existe o no está activa.',

            // ✅ MENSAJES FECHAS
            'registration_date.required' => 'La fecha de registro es obligatoria.',
            'registration_date.date'     => 'La fecha de registro no es válida.',

            'expiration_date.required'   => 'La fecha de vencimiento es obligatoria.',
            'expiration_date.date'       => 'La fecha de vencimiento no es válida.',
            'expiration_date.after_or_equal' =>
                'La fecha de vencimiento debe ser mayor o igual a la fecha de registro.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException(
            $validator,
            response()->json([
                'errors' => $validator->errors()
            ], 422)
        );
    }
}
