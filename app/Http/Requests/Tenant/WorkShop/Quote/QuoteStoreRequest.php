<?php

namespace App\Http\Requests\Tenant\WorkShop\Quote;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

class QuoteStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => [
                'required',
                Rule::exists('landlord.customers', 'id')->where('status', 'ACTIVO'),
            ],

            'vehicle_id' => [
                'nullable',
                Rule::exists('vehicles', 'id')->where('status', 'ACTIVO'),
            ],

            'plate' => [
                'nullable',
                'string',
                'min:6',
                'max:8',
            ],

            'expiration_date' => [
                'nullable',
                'date',
                'after:today',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required' => 'El cliente es obligatorio.',
            'client_id.exists'   => 'El cliente seleccionado no existe o no está activo.',

            'vehicle_id.exists'  => 'El vehículo seleccionado no existe o no está activo.',

            'plate.min'      => 'La placa debe tener al menos 6 caracteres.',
            'plate.max'      => 'La placa no debe exceder los 8 caracteres.',

            'expiration_date.date'  => 'La fecha de expiración debe ser una fecha válida.',
            'expiration_date.after' => 'La fecha de expiración debe ser mayor a la fecha actual.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
