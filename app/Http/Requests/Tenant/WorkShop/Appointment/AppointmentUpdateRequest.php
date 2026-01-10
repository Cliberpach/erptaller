<?php

namespace App\Http\Requests\Tenant\WorkShop\Appointment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Carbon\Carbon;

class AppointmentUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->replace(
            collect($this->all())->mapWithKeys(function ($value, $key) {

                if (str_ends_with($key, '_event_edit')) {
                    $newKey = str_replace('_event_edit', '', $key);
                    return [$newKey => $value];
                }

                return [$key => $value];
            })->toArray()
        );
    }

    public function rules(): array
    {
        return [

            'name' => [
                'required',
                'string',
                'max:500',
            ],

            'customer_id' => [
                'required',
                Rule::exists('landlord.customers', 'id')->where('status', 'ACTIVO'),
            ],

            'vehicle_id' => [
                'nullable',
                Rule::exists('vehicles', 'id')->where('status', 'ACTIVO'),
            ],

            'type_calendar' => [
                'required',
                Rule::in(['PERSONAL', 'TRABAJO']),
            ],

            'start_date' => [
                'required',
                'date',
            ],

            'start_time' => [
                'required',
                'date_format:H:i',
            ],

            'end_date' => [
                'required',
                'date',
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

    /**
     * Validaciones avanzadas
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            if (
                $this->start_date &&
                $this->start_time &&
                $this->end_date &&
                $this->end_time
            ) {
                $start = Carbon::parse("{$this->start_date} {$this->start_time}");
                $end   = Carbon::parse("{$this->end_date} {$this->end_time}");

                if ($end->lessThanOrEqualTo($start)) {
                    $validator->errors()->add(
                        'end_time',
                        'La fecha y hora de fin debe ser mayor a la de inicio.'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [

            'name.required' => 'El nombre del evento es obligatorio.',
            'name.max'      => 'El nombre del evento no debe superar los 500 caracteres.',

            'customer_id.required' => 'El cliente es obligatorio.',
            'customer_id.exists'   => 'El cliente seleccionado no existe o no está activo.',

            'vehicle_id.exists'  => 'El vehículo seleccionado no existe o no está activo.',

            'type_calendar.required' => 'El tipo de calendario es obligatorio.',
            'type_calendar.in'       => 'El tipo de calendario debe ser PERSONAL o TRABAJO.',

            'start_date.required' => 'La fecha de inicio es obligatoria.',
            'start_date.date'     => 'La fecha de inicio no es válida.',

            'start_time.required'    => 'La hora de inicio es obligatoria.',
            'start_time.date_format' => 'La hora de inicio debe tener el formato HH:MM.',

            'end_date.required' => 'La fecha de fin es obligatoria.',
            'end_date.date'     => 'La fecha de fin no es válida.',
            'end_date.after_or_equal' =>
            'La fecha de fin no puede ser menor a la fecha de inicio.',

            'end_time.required'    => 'La hora de fin es obligatoria.',
            'end_time.date_format' => 'La hora de fin debe tener el formato HH:MM.',

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
