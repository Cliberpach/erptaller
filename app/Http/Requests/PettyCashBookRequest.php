<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class PettyCashBookRequest extends FormRequest
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
            //
            'idCaja' => 'required|not_in:0',
            'idTurno' => 'required|not_in:0', // Valida que el valor seleccionado en el select no sea igual a 0
            'cantidadInicial' => 'required|numeric|min:0', // Valida que el input no esté vacío, sea un número y mayor o igual a 0
        ];
    }

     /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'idCaja.required' => 'El campo Caja es obligatorio.',
            'idCaja.not_in' => 'Por favor, selecciona una opción válida para el campo Caja.',
            'idTurno.required' => 'El campo Turno es obligatorio.',
            'idTurno.not_in' => 'Por favor, selecciona una opción válida para el campo Turno.',
            'cantidadInicial.required' => 'El campo Saldo inicial es obligatorio.',
            'cantidadInicial.numeric' => 'El campo Saldo inicial debe ser un número.',
            'cantidadInicial.min' => 'El campo Saldo inicial debe ser mayor o igual a 0.',
        ];
    }
    
    protected function failedValidation(Validator $validator) {
        $errors = $validator->errors()->toArray();
    
        // Lanzar una respuesta JSON con los mensajes de error originales
        throw new HttpResponseException(response()->json(
            [
                'tipo' => 'error',
                'errors' => $errors,
            ], 422));
    }
}
