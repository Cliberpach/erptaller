<?php

namespace App\Http\Requests\Tenant\Cash\Cash;

use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class CashUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $clean = [];

        foreach ($this->all() as $key => $value) {
            if (is_string($key) && str_ends_with($key, '_edit')) {
                $newKey = substr($key, 0, -5); // quita "_edit"
                $clean[$newKey] = $value;
            } else {
                $clean[$key] = $value;
            }
        }

        if (isset($clean['name'])) {
            $clean['name'] = is_string($clean['name']) ? trim($clean['name']) : $clean['name'];
        }

        $this->merge($clean);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $id = $this->route('id'); // Ignorar ID actual en update

        return [
            'name' => 'required|string|max:255|unique:petty_cashes,name,' . $id,
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El campo nombre es obligatorio.',
            'name.string'   => 'El campo nombre debe ser una cadena de texto.',
            'name.unique'   => 'El nombre ya existe.',
            'name.max'      => 'El nombre no debe exceder los 255 caracteres.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
