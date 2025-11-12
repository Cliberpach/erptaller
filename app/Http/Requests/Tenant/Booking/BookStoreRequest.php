<?php

namespace App\Http\Requests\Tenant\Booking;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class BookStoreRequest extends FormRequest
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
            'field_id'          =>  'required|exists:fields,id',
            'schedule_id'       =>  'required|exists:schedules,id',
            'document_number'   =>  'required|string|size:8',
            'name'              =>  'required|string|max:255',
            'phone'             =>  'required|string|max:20',
            'payment_type'      =>  'required|string',
            'payment'           =>  'nullable|numeric|min:0',
            'voucher'           =>  'nullable|file|mimes:jpg,jpeg,png,pdf',
            'date'              =>  'required|date',
            'nro_hours'         => 'required|numeric|gt:0|in:0.5,1,1.5,2,2.5,3,3.5,4,4.5,5,5.5,6',
            'modality'          => ['required', Rule::in(['7v7', '9v9', '11vs11'])],

        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'field_id.required'         => 'El campo es obligatorio.',
            'field_id.exists'           => 'El campo seleccionado no es válido.',
            
            'schedule_id.required'      => 'El horario es obligatorio.',
            'schedule_id.exists'        => 'El horario seleccionado no es válido.',
            
            'document_number.required'  => 'El número de documento es obligatorio.',
            'document_number.string'    => 'El número de documento debe ser una cadena de texto.',
            'document_number.size'      => 'El número de documento debe tener exactamente 8 caracteres.',
            
            'name.required'             => 'El nombre es obligatorio.',
            'name.string'               => 'El nombre debe ser una cadena de texto.',
            'name.max'                  => 'El nombre no debe exceder los 255 caracteres.',
            
            'phone.required'            => 'El número de teléfono es obligatorio.',
            'phone.string'              => 'El número de teléfono debe ser una cadena de texto.',
            'phone.max'                 => 'El número de teléfono no debe exceder los 20 caracteres.',
            
            'payment_type.required'     => 'El tipo de pago es obligatorio.',
            'payment_type.string'       => 'El tipo de pago debe ser una cadena de texto.',
            'payment.numeric'           => 'El pago debe ser un número.',
            'payment.min'               => 'El pago debe ser al menos 0.',
            
            'voucher.file'              => 'El comprobante debe ser un archivo.',
            'voucher.mimes'             => 'El comprobante debe estar en formato JPG, JPEG, PNG o PDF.',
            'voucher.required_unless'   => 'El comprobante es obligatorio a menos que el tipo de pago sea EFECTIVO.',
            
            'date.required'             => 'La fecha es obligatoria.',
            'date.date'                 => 'La fecha debe ser una fecha válida.',

            'nro_hours.required' => 'El número de horas es obligatorio.',
            'nro_hours.numeric'  => 'El número de horas debe ser un valor numérico.',
            'nro_hours.gt'       => 'El número de horas debe ser mayor a 0.',
            'nro_hours.in'       => 'El número de horas debe ser entre 0.5 a 6, cada 30 minutos',
        ];
    }


    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
