<?php

namespace App\Http\Requests\Tenant\Maintenance\BankAccount;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

class BankAccountStoreRequest extends FormRequest
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
     */
    public function rules()
    {
        return [
            'holder' => [
                'required',
                'string',
                'max:200',
            ],
            'bank_id' => [
                'required',
                Rule::exists('landlord.general_table_details', 'id')->where('status', 'ACTIVO'),
            ],
            'currency' => [
                'required',
                'in:SOLES,DOLARES',
            ],
            'account_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('bank_accounts', 'account_number')->where(
                    fn($query) => $query->where('status', 'ACTIVO')
                ),
            ],
            'cci' => [
                'required',
                'string',
                'max:100',
                Rule::unique('bank_accounts', 'cci')->where(
                    fn($query) => $query->where('status', 'ACTIVO')
                ),
            ],
            'phone' => [
                'nullable',
                'regex:/^\d{1,20}$/',
            ],
        ];
    }

    public function messages()
    {
        return [
            'bank_id.required'       => 'El banco es obligatorio.',
            'bank_id.exists'         => 'El banco seleccionado no es válido o no está activo.',

            'currency.required'      => 'La moneda es obligatoria.',
            'currency.in'            => 'La moneda debe ser SOLES o DÓLARES.',

            'account_number.required'=> 'El número de cuenta es obligatorio.',
            'account_number.max'     => 'El número de cuenta no puede superar los 100 caracteres.',
            'account_number.unique'  => 'El número de cuenta ya está registrado en una cuenta activa.',

            'cci.required'           => 'El CCI es obligatorio.',
            'cci.max'                => 'El CCI no puede superar los 100 caracteres.',
            'cci.unique'             => 'El CCI ya está registrado en una cuenta activa.',

            'phone.regex'            => 'El celular debe contener solo números y tener un máximo de 20 dígitos.',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'account_number' => preg_replace('/\s+/', '', $this->account_number),
            'cci'            => preg_replace('/\s+/', '', $this->cci),
        ]);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
