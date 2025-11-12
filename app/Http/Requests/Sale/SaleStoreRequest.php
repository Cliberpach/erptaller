<?php

namespace App\Http\Requests\Sale;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

class SaleStoreRequest extends FormRequest
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
            'type_sale'     => 'required|in:80,3,1', 
            'customer_id' => [
                'required',
                Rule::exists('landlord.customers', 'id')->where('status', 'ACTIVO'),
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type_sale.required'    => 'El tipo de venta es obligatorio.',
            'type_sale.in'          => 'El tipo de venta debe ser uno de los siguientes: 127, 128, 129.',
            
            'customer_id.required'  => 'El cliente es obligatorio.',
            'customer_id.exists'    => 'El cliente seleccionado debe estar activo.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
