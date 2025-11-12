<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
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
            'document_number' => 'required|string|size:8|unique:customers,document_number|max:255',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
        ];
    }


    public function messages(): array
    {
        return [
            'document_number.required' => 'El número de documento es obligatorio.',
            'document_number.size' => 'El número de documento debe tener exactamente 8 caracteres.',
            'document_number.unique' => 'El número de documento ya existe.',
            'name.required' => 'El nombre es obligatorio.',
            'phone.required' => 'El teléfono es obligatorio.',
        ];
    }
}
