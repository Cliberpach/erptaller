<?php

namespace App\Http\Requests\Tenant\ReservationDocument;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\Landlord\GeneralTable\GeneralTableDetail;

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

            // Multi-sede Capa C: validar contra general_table_details (misma fuente que SaleService;
            // document_types está vacía). Solo boleta('03')/factura('01') desde reserva. El id se
            // resuelve vivo por symbol SUNAT (no se hardcodea código->id).
            'document_invoice' => [
                'required',
                function ($attribute, $value, $fail) {
                    $valido = GeneralTableDetail::where('id', $value)
                        ->where('general_table_id', 4)
                        ->whereIn('symbol', ['01', '03'])
                        ->exists();
                    if (! $valido) {
                        $fail('SOLO SE ACEPTAN BOLETAS O FACTURAS.');
                    }
                },
            ],

            'document_number' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {
                    $symbol = optional(GeneralTableDetail::find($this->input('document_invoice')))->symbol;

                    if ($symbol === '01' && strlen($value) != 11) {
                        return $fail('El número de documento debe tener 11 dígitos para facturas.');
                    }
                    if ($symbol === '03' && strlen($value) != 8) {
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
