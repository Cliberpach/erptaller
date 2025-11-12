<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */

    
     public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation()
    {
        $productData = $this->input('productData');

        if (is_string($productData)) {
            $this->merge([
                'productData' => json_decode($productData, true),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        $routeName = $this->route()->getName();

        $rules = [
            'productData.sale_price'        => 'required|numeric|min:0',
            'productData.purchase_price'    => 'required|numeric|min:0',
            'productData.stock'             => 'required|integer|min:0',
            'productData.stock_min'         => 'required|integer|min:0',
            'productData.brand_id'          => 'not_in:0',
            'productData.category_id'       => 'not_in:0',
        ];

        if ($routeName == 'tenant.inventarios.productos.store') {
            $rules['productData.name'] = 'required|unique:products,name';
        }
        if ($routeName == 'tenant.inventarios.productos.update') {
            $rules['productData.name'] = 'required';
        }


        return $rules;
            
    }

    public function messages()
    {
        return [
            'productData.name.required' => 'Nombre obligatorio.',
            'productData.name.unique' => 'Nombre ya existe.',
            'productData.sale_price.required' => 'Precio de Venta obligatorio.',
            'productData.sale_price.numeric' => 'Precio de Venta debe ser numérico.',
            'productData.sale_price.min' => 'Precio de Venta no puede ser negativo.',
            'productData.purchase_price.required' => 'Precio de Compra obligatorio.',
            'productData.purchase_price.numeric' => 'Precio de Compra debe ser numérico.',
            'productData.purchase_price.min' => 'Precio de Compra no puede ser negativo.',
            'productData.stock.required' => 'Stock obligatorio.',
            'productData.stock.integer' => 'Stock debe ser entero.',
            'productData.stock.min' => 'Stock no puede ser negativo.',
            'productData.stock_min.required' => 'Stock Mínimo obligatorio.',
            'productData.stock_min.integer' => 'Stock Mínimo debe ser entero.',
            'productData.stock_min.min' => 'Stock Mínimo no puede ser negativo.',
            'productData.brand_id.not_in' => 'Debes seleccionar una marca válida.',
            'productData.category_id.not_in' => 'Debes seleccionar una categoría válida.'

        ];
    
    }
    

    protected function failedValidation(Validator $validator) {
        $errors = $validator->errors()->toArray();
    
        // Lanzar una respuesta JSON con los mensajes de error originales
        throw new HttpResponseException(response()->json(
            [
                'type' => 'error',
                'errors' => $errors,
                'ruta' => $this->route()->getName(),
            ], 422));
    }

}
