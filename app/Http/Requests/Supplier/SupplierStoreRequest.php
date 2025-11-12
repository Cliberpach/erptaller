<?php

namespace App\Http\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class SupplierStoreRequest extends FormRequest
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
        $rules = [
            'tipo_documento' => [
                'required', 
                'exists:types_identity_documents,id'
            ],
           'nro_documento' => [
                'required', 
                'numeric', 
                function ($attribute, $value, $fail) {
                    $tipoDocumento = $this->input('tipo_documento');
                    if ($tipoDocumento == 1 && strlen($value) != 8) {
                        $fail('El número de documento debe tener 8 dígitos si el tipo de documento es DNI.');
                    }
                    if ($tipoDocumento == 2 && strlen($value) != 11) {
                        $fail('El número de documento debe tener 11 dígitos si el tipo de documento es RUC.');
                    }
                },
                'unique:suppliers,document_number,NULL,id,estado,ACTIVO'
            ],
            'nombre' => 'required|max:200',
            'direccion' => 'nullable|max:150',
            'telefono' => [
                'nullable', 
                'max:20', 
                'regex:/^[0-9]+$/'
            ],
            'correo' => 'nullable|email|max:150',
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'tipo_documento.required'   => 'El tipo de documento es obligatorio.',
            'tipo_documento.exists'     => 'El tipo de documento seleccionado no es válido o no está activo.',
            
            'nro_documento.required'    => 'El número de documento es obligatorio.',
            'nro_documento.numeric'     => 'El número de documento debe ser numérico.',
            
            'nombre.required'           => 'El nombre es obligatorio.',
            'nombre.max'                => 'El nombre no puede exceder de 200 caracteres.',
            
            'direccion.max'             => 'La dirección no puede exceder de 150 caracteres.',
            
            'telefono.max'              => 'El teléfono no puede exceder de 20 caracteres.',
            'telefono.regex'            => 'El teléfono solo puede contener números.',
            
            'correo.email'              => 'El correo electrónico debe tener un formato válido.',
            'correo.max'                => 'El correo electrónico no puede exceder de 150 caracteres.',
            
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
