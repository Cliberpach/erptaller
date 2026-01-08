<?php

namespace App\Http\Requests\Tenant\Notification;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Validation\Validator as ValidationValidator;

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
            'advance_days' => [
                'required',
                'integer',
                'min:0',
                'max:30',
            ]
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

            'advance_days.required' => 'Los días anticipados son obligatorios.',
            'advance_days.integer'  => 'Los días anticipados deben ser un número entero.',
            'advance_days.min'      => 'Los días anticipados no pueden ser negativos.',
            'advance_days.max'      => 'Los días anticipados no pueden ser mayores a 30.',

        ];
    }


    public function withValidator(ValidationValidator $validator)
    {
        $validator->after(function ($validator) {
            if ($this->notice_date !== null && $this->advance_days !== null) {

                $noticeDate = Carbon::parse($this->notice_date);
                $advanceDays = (int) $this->advance_days;

                // notice_date - advance_days
                $advanceDate = $noticeDate->copy()->subDays($advanceDays);

                if ($advanceDate->lt(Carbon::today())) {
                    $validator->errors()->add(
                        'advance_days',
                        'La fecha anticipada resultante no puede ser menor a la fecha actual.'
                    );
                }
            }
        });
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
