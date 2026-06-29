<?php

namespace App\Http\Requests\Tenant\WorkShop\Service;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;

class ServicioImportExcelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'servicios_import_excel' => 'required|file|mimes:xlsx,xls'
        ];
    }

    public function messages()
    {
        return [
            'servicios_import_excel.required'  => 'Es necesario subir un archivo Excel.',
            'servicios_import_excel.file'      => 'El archivo debe ser un documento válido.',
            'servicios_import_excel.mimes'     => 'El archivo debe tener un formato Excel válido (xlsx, xls).'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
