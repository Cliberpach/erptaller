<?php

namespace App\Http\Requests\Tenant\Inventory\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

class ProductoImportExcelRequest extends FormRequest
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
    public function rules()
    {
        return [
            'productos_import_excel' => 'required|file|mimes:xlsx,xls'
        ];
    }

    /**
     * Get custom messages for validation errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'productos_import_excel.required'  => 'Es necesario subir un archivo Excel.',
            'productos_import_excel.file'      => 'El archivo debe ser un documento válido.',
            'productos_import_excel.mimes'     => 'El archivo debe tener un formato Excel válido (xlsx, xls).'
        ];
    }


    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
