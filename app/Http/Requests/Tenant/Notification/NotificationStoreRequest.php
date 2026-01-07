<?php

namespace App\Http\Requests\Tenant\Notification;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class NotificationStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        $today = Carbon::today()->format('Y-m-d');

        return [
            'name' => [
                'required',
                'string',
                'max:500',
            ],
            'description' => [
                'nullable',
                'string',
                'max:500',
            ],
            'notice_date' => [
                'required',
                'date',
                'after_or_equal:' . $today,
            ],
            'advance_date' => [
                'required',
                'date',
                'after_or_equal:' . $today,
                'after_or_equal:notice_date',
            ],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la alerta es obligatorio.',
            'name.max' => 'El nombre de la alerta no puede superar los 500 caracteres.',

            'description.max' => 'La descripción no puede superar los 500 caracteres.',

            'notice_date.required' => 'La fecha de notificación es obligatoria.',
            'notice_date.date' => 'La fecha de notificación no es válida.',
            'notice_date.after_or_equal' => 'La fecha de notificación debe ser igual o mayor a la fecha actual.',

            'advance_date.required' => 'La fecha anticipada es obligatoria.',
            'advance_date.date' => 'La fecha anticipada no es válida.',
            'advance_date.after_or_equal' => 'La fecha anticipada debe ser igual o mayor a la fecha actual y a la fecha de notificación.',
        ];
    }

    /**
     * Return JSON on validation error.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'message' => 'Errores de validación',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
