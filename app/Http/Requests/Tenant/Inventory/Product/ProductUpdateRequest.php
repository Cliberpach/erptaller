<?php

namespace App\Http\Requests\Tenant\Inventory\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class ProductUpdateRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'min:3',
                'max:160',
                Rule::unique('products', 'name')->ignore($this->route('id')) ->where('status', 'ACTIVE'),
            ],
            'description' => 'nullable|string|max:200',
            'sale_price' => 'required|numeric|min:1|max:99999999',
            'purchase_price' => 'required|numeric|min:1|max:99999999',
            'stock_min' => 'required|integer|min:0|max:99999999',
            'code_factory' => 'nullable|alpha_num|size:10',
            'code_bar' => 'nullable|alpha_num|min:6|max:20',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where('status', 'ACTIVE')
            ],
            'brand_id' => [
                'required',
                Rule::exists('brands', 'id')->where('status', 'ACTIVE')
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.min' => 'El nombre debe tener al menos 3 caracteres.',
            'name.max' => 'El nombre no debe exceder los 160 caracteres.',
            'name.unique' => 'Ya existe un producto con ese nombre en estado activo.',

            'description.max' => 'La descripción no debe exceder los 200 caracteres.',

            'sale_price.required' => 'El precio de venta es obligatorio.',
            'sale_price.numeric' => 'El precio de venta debe ser un número entero.',
            'sale_price.min' => 'El precio de venta debe ser mayor a 0.',
            'sale_price.max' => 'El precio de venta no debe exceder 8 dígitos.',

            'purchase_price.required' => 'El precio de compra es obligatorio.',
            'purchase_price.numeric' => 'El precio de compra debe ser un número entero.',
            'purchase_price.min' => 'El precio de compra debe ser mayor a 0.',
            'purchase_price.max' => 'El precio de compra no debe exceder 8 dígitos.',

            'stock_min.required' => 'El stock mínimo es obligatorio.',
            'stock_min.integer' => 'El stock mínimo debe ser un número entero.',
            'stock_min.min' => 'El stock mínimo no puede ser negativo.',
            'stock_min.max' => 'El stock mínimo no debe exceder 8 dígitos.',

            'code_factory.alpha_num' => 'El código de fábrica debe ser alfanumérico.',
            'code_factory.size' => 'El código de fábrica debe tener exactamente 10 caracteres.',

            'code_bar.alpha_num' => 'El código de barras debe ser alfanumérico.',
            'code_bar.min' => 'El código de barras debe tener al menos 6 caracteres.',
            'code_bar.max' => 'El código de barras no debe exceder los 20 caracteres.',

            'image.image' => 'El archivo debe ser una imagen.',
            'image.mimes' => 'La imagen debe ser de tipo: jpg, jpeg, png o webp.',
            'image.max' => 'La imagen no debe pesar más de 2MB.',

            'category_id.required' => 'La categoría es obligatoria.',
            'category_id.exists' => 'La categoría seleccionada no es válida.',

            'brand_id.required' => 'La marca es obligatoria.',
            'brand_id.exists' => 'La marca seleccionada no es válida.',
        ];
    }

    protected function prepareForValidation()
    {
        $cleaned = [];

        foreach ($this->all() as $key => $value) {
            if (str_ends_with($key, '_edit')) {
                $cleaned[substr($key, 0, -5)] = $value;
            } else {
                $cleaned[$key] = $value;
            }
        }

        $this->merge($cleaned);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
