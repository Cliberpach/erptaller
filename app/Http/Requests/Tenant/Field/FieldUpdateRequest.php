<?php

namespace App\Http\Requests\Tenant\Field;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FieldUpdateRequest extends FormRequest
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
            'type_field_id' => [
                'required',
                Rule::exists('type_fields', 'id')->where(function ($query) {
                    $query->where('estado', 'ACTIVO');
                }),
            ],
            'field' => [
                'required',
                'string',
                'max:255',
                Rule::unique('fields', 'field')
                    ->where('isDeleted', 0)
                    ->ignore($this->route('id')), 
            ],
            'day_price'     => 'required|numeric',
            'night_price'   => 'required|numeric',
            'location'      => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'type_field_id.required'    => 'El campo "type_field_id" es obligatorio.',
            'type_field_id.exists'      => 'El "type_field_id" debe existir en la tabla type_fields con estado ACTIVO.',
           
            'field.required'            => 'El campo "field" es obligatorio.',
            'field.unique'              => 'El valor del campo "field" ya existe en la tabla para un registro no eliminado.',
            'field.max'                 => 'El campo "field" no debe exceder los 255 caracteres.',
            
            'day_price.required'        => 'El precio diurno ("day_price") es obligatorio.',
            'day_price.numeric'         => 'El precio diurno ("day_price") debe ser un número.',
            
            'night_price.required'      => 'El precio nocturno ("night_price") es obligatorio.',
            'night_price.numeric'       => 'El precio nocturno ("night_price") debe ser un número.',
            
            'location.max'              => 'El campo "location" no debe exceder los 255 caracteres.',
        ];
    }
}
