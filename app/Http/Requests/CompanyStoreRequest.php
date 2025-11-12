<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompanyStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'domain'                    => 'required',
            'ruc' => [
                'required',
                Rule::unique('companies')
                    ->where(function ($query) {
                        $query->where('status', 1);
                    }),
            ],
            'razon_social'              => 'required',
            'razon_social_abreviada'    => 'required',
            'plan_id'                   => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'domain.required'                   => 'El nombre del dominio es requerido',
            'ruc.required'                      => 'El campo RUC es requerido',
            'ruc.unique'                        => 'El RUC ingresado ya está registrado para una empresa activa.',
            'razon_social.required'             => 'El campo razón social es requerido',
            'razon_social_abreviada.required'   => 'El campo razón social abreviada es requerido',
            'plan_id.required'                  => 'El plan es requerido',
        ];
    }
}
