<?php

namespace App\Http\Requests\Company;

use App\Models\DocumentType;
use App\Models\Landlord\GeneralTable\GeneralTableDetail;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

class CompanyNumerationRequest extends FormRequest
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
            'billing_type_document' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    if (!GeneralTableDetail::where('id', $value)->where('status', 'ACTIVO')->exists()) {
                        $fail("El $attribute seleccionado no es válido.");
                    }
                },
            ],
            'serie'         => 'required|string|max:255',
            'start_number'  => 'required|integer|min:1|max:99999999',
        ];
    }

    /**
     * Custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'billing_type_document.required'    => 'El tipo de documento de facturación es obligatorio.',
            'billing_type_document.integer'     => 'El tipo de documento de facturación debe ser un número entero.',
            'billing_type_document.in'          => 'El tipo de documento de facturación debe ser uno de los siguientes valores: 1, 3, 6, 7, 9, 80.',

            'serie.required'                    => 'La serie es obligatoria.',

            'start_number.required'             => 'El número de inicio es obligatorio.',
            'start_number.integer'              => 'El número de inicio debe ser un número entero.',
            'start_number.min'                  => 'El número de inicio debe ser al menos 1.',
            'start_number.max'                  => 'El número de inicio no debe tener más de 8 dígitos.',
        ];
    }


    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
