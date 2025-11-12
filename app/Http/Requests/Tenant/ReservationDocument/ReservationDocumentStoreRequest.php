<?php

namespace App\Http\Requests\Tenant\ReservationDocument;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class ReservationDocumentStoreRequest extends FormRequest
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

            'document_invoice' => [
                'required', 
                Rule::exists('document_types', 'id')->whereIn('id', [1, 3]),
            ],

            'document_number' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {
                    $documentInvoice = $this->input('document_invoice');
                    
                    if ($documentInvoice == 1 && strlen($value) != 11) {
                        return $fail('El número de documento debe tener 11 dígitos para facturas.');
                    }
                    
                    if ($documentInvoice == 3 && strlen($value) != 8) {
                        return $fail('El número de documento debe tener 8 dígitos para boletas.');
                    }
                },
            ],
        ];
    }

    /**
     * Custom messages for validation.
     */
    public function messages(): array
    {
        return [
            'document_invoice.required' => 'El tipo de documento es obligatorio.',
            'document_invoice.exists'   => 'El tipo de documento seleccionado no es válido.',
            'document_invoice.in'       => 'SOLO SE ACEPTAN BOLETAS O FACTURAS.',

            'document_number.required'  => 'El número de documento es obligatorio.',
            'document_number.numeric'   => 'El número de documento debe ser un valor numérico.',
        ];
    }


    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
