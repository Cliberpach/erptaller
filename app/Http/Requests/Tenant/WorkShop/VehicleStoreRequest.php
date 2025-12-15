<?php

namespace App\Http\Requests\Tenant\WorkShop;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

class VehicleStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'plate'     => $this->input('plate') ?? $this->input('plate_mdlvehicle'),
            'model_id'  => $this->input('model_id') ?? $this->input('model_id_mdlvehicle'),
            'year_id'   => $this->input('year_id') ?? $this->input('year_id_mdlvehicle'),
            'client_id' => $this->input('client_id') ?? $this->input('client_id_mdlvehicle'),
            'color_id' => $this->input('color_id') ?? $this->input('color_id_mdlvehicle'),
            'observation' => $this->input('observation') ?? $this->input('observation_mdlvehicle'),
        ]);
    }

    public function rules(): array
    {
        return [

            // CLIENTE: obligatorio, debe existir en landlord.customers con status ACTIVE
            'client_id' => [
                'required',
                Rule::exists('landlord.customers', 'id')->where(function ($q) {
                    $q->where('status', 'ACTIVO');
                }),
            ],

            // PLACA: obligatorio, tamaño 6 a 8, único en vehicles del tenant con status ACTIVE
            'plate' => [
                'required',
                'string',
                'min:6',
                'max:8',
                Rule::unique('vehicles', 'plate')->where(function ($q) {
                    $q->where('status', 'ACTIVE');
                }),
            ],

            // MODELO: obligatorio, debe existir en landlord.models con status ACTIVE
            'model_id' => [
                'required',
                Rule::exists('landlord.models', 'id')->where(function ($q) {
                    $q->where('status', 'ACTIVE');
                }),
            ],

            // AÑO: obligatorio, debe existir en landlord.years con status ACTIVE
            'year_id' => [
                'nullable',
                Rule::exists('landlord.years', 'id')->where(function ($q) {
                    $q->where('status', 'ACTIVE');
                }),
            ],

            // OBSERVACIÓN: opcional, máximo 300 caracteres
            'observation' => [
                'nullable',
                'string',
                'max:300',
            ],

            // VIN: opcional, máximo 160 caracteres
            'vin' => [
                'nullable',
                'string',
                'max:160',
            ],

            'serie' => [
                'nullable',
                'string',
                'max:160',
            ],
        ];
    }

    public function messages(): array
    {
        return [

            // client_id
            'client_id.required' => 'El cliente es obligatorio.',
            'client_id.exists'   => 'El cliente seleccionado no existe o no está activo.',

            // plate
            'plate.required' => 'La placa es obligatoria.',
            'plate.min'      => 'La placa debe tener al menos 6 caracteres.',
            'plate.max'      => 'La placa no puede exceder los 8 caracteres.',
            'plate.unique'   => 'Ya existe un vehículo activo con esta placa.',

            // model_id
            'model_id.required' => 'El modelo es obligatorio.',
            'model_id.exists'   => 'El modelo seleccionado no existe o no está activo.',

            // year_id
            'year_id.exists'   => 'El año seleccionado no existe o no está activo.',

            // observation
            'observation.max' => 'La observación no puede tener más de 300 caracteres.',

            'vin.max' => 'El vin no puede tener más de 160 caracteres.',
            'serie.max' => 'La serie no puede tener más de 160 caracteres.',

        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
