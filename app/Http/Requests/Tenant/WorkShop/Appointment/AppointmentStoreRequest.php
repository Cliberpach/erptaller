<?php

namespace App\Http\Requests\Tenant\WorkShop\Appointment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

class AppointmentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'name'          => $this->name_event,
            'type_calendar' => $this->type_calendar_event,
            'start_date'    => $this->start_date_event,
            'start_time'    => $this->start_time_event,
            'end_date'      => $this->end_date_event,
            'end_time'      => $this->end_time_event,
            'location'      => $this->location_event,
            'description'   => $this->description_event,
        ]);
    }

    public function rules(): array
    {
        return [

            'name' => [
                'required',
                'string',
                'max:500',
            ],

            'type_calendar' => [
                'required',
                Rule::in(['PERSONAL', 'TRABAJO']),
            ],

            'start_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],

            'start_time' => [
                'required',
                'date_format:H:i',
            ],

            'end_date' => [
                'required',
                'date',
                'after_or_equal:today',
                'after_or_equal:start_date',
            ],

            'end_time' => [
                'required',
                'date_format:H:i',
            ],

            'location' => [
                'nullable',
                'string',
                'max:500',
            ],

            'description' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }


    public function messages(): array
    {
        return [

            'name.required' => 'El nombre del evento es obligatorio.',
            'name.max'      => 'El nombre del evento no debe superar los 500 caracteres.',

            'type_calendar.required' => 'El tipo de calendario es obligatorio.',
            'type_calendar.in'       => 'El tipo de calendario debe ser PERSONAL o TRABAJO.',

            'start_date.required'        => 'La fecha de inicio es obligatoria.',
            'start_date.date'            => 'La fecha de inicio no es válida.',
            'start_date.after_or_equal'  => 'La fecha de inicio no puede ser menor a hoy.',

            'start_time.required'     => 'La hora de inicio es obligatoria.',
            'start_time.date_format'  => 'La hora de inicio debe tener el formato HH:MM.',

            'end_date.required'        => 'La fecha de fin es obligatoria.',
            'end_date.date'            => 'La fecha de fin no es válida.',
            'end_date.after_or_equal'  => 'La fecha de fin no puede ser menor a la fecha de inicio.',

            'end_time.required'     => 'La hora de fin es obligatoria.',
            'end_time.date_format'  => 'La hora de fin debe tener el formato HH:MM.',

            'location.max' => 'La ubicación no debe superar los 500 caracteres.',

            'description.max' => 'La descripción no debe superar los 500 caracteres.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
