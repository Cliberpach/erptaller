<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

class CompanyInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->input('department')) {
            $this->merge([
                'department' => str_pad($this->input('department'), 2, '0', STR_PAD_LEFT),
            ]);
        }

        if ($this->input('province')) {
            $this->merge([
                'province' => str_pad($this->input('province'), 4, '0', STR_PAD_LEFT),
            ]);
        }

        if ($this->input('district')) {
            $this->merge([
                'district' => str_pad($this->input('district'), 6, '0', STR_PAD_LEFT),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'department'     => 'required|exists:departments,id',
            'province'       => 'required|exists:provinces,id',
            'district'       => 'required|exists:districts,id',
            'urbanization'   => 'required|string|max:120',
            'local_code'     => 'required|string|max:8',
            'sol_user'       => 'required|string|max:120',
            'sol_pass'       => 'required|string|max:120',
            'api_user_gree'   => 'nullable|string|max:120',
            'sol_pass_gre'   => 'nullable|string|max:120',
            'certificate'    => 'required|file|mimes:pem,pfx,p12',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'department.required'    => 'El campo Departamento es obligatorio.',
            'department.exists'      => 'El Departamento seleccionado no existe en la base de datos.',
           
            'province.required'      => 'El campo Provincia es obligatorio.',
            'province.exists'        => 'La Provincia seleccionada no existe en la base de datos.',
            
            'district.required'      => 'El campo Distrito es obligatorio.',
            'district.exists'        => 'El Distrito seleccionado no existe en la base de datos.',
            
            'urbanization.required'  => 'El campo Urbanización es obligatorio.',
            'urbanization.max'       => 'El campo Urbanización no debe exceder 120 caracteres.',
            
            'local_code.required'    => 'El campo Código Local es obligatorio.',
            'local_code.max'         => 'El campo Código Local no debe exceder 8 caracteres.',
           
            'sol_user.required'      => 'El campo Usuario SOL es obligatorio.',
            'sol_user.max'           => 'El campo Usuario SOL no debe exceder 120 caracteres.',
            
            'sol_pass.required'      => 'El campo Contraseña SOL es obligatorio.',
            'sol_pass.max'           => 'El campo Contraseña SOL no debe exceder 120 caracteres.',
            
            'api_user_gree.max'       => 'El campo Usuario SOL GRE no debe exceder 120 caracteres.',
            'sol_pass_gre.max'       => 'El campo Contraseña SOL GRE no debe exceder 120 caracteres.',
            
            'certificate.required'   => 'El campo Certificado es obligatorio.',
            'certificate.mimes'      => 'El Certificado debe ser un archivo con extensión .pem, .pfx o .p12.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'errors' => $validator->errors()
        ], 422));
    }

}
