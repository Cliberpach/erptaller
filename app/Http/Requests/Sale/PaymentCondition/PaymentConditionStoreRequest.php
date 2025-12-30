<?php

namespace App\Http\Requests\Sale\PaymentCondition;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentConditionStoreRequest extends FormRequest
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
            'tipo' => ['required', 'in:1,2'],
            'nro_dias' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    // Validar que nro_dias sea 0 si tipo es 1
                    if (request('tipo') == 1 && $value != 0) {
                        $fail('El campo nro_dias debe ser 0 cuando el tipo es CONTADO.');
                    }
                    // Validar que nro_dias sea mayor a 0 si tipo es 2
                    if (request('tipo') == 2 && $value <= 0) {
                        $fail('El campo nro_dias debe ser mayor a 0 cuando el tipo es CRÉDITO.');
                    }

                    if (request('tipo') == 1) {
                        $exists = DB::table('payment_conditions')
                            ->where('type', 'CONTADO')
                            ->where('status', 'ACTIVO')
                            ->exists();
                        if ($exists) {
                            $fail('Ya existe un registro con el tipo CONTADO.');
                        }
                    }

                    if (request('tipo') == 2) {
                        $exists = DB::table('payment_conditions')
                            ->where('type', 'CRÉDITO')
                            ->where('status', 'ACTIVO')
                            ->where('nro_days', $value)
                            ->exists();
                        if ($exists) {
                            $fail('Ya existe un registro con el mismo número de días en la modalidad CRÉDITO.');
                        }
                    }

                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'tipo.required'     => 'El campo tipo es obligatorio.',
            'tipo.in'           => 'El campo tipo debe ser CONTADO o CRÉDITO.',
            'nro_dias.required' => 'El campo nro_dias es obligatorio.',
            'nro_dias.integer'  => 'El campo nro_dias debe ser un número entero.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
