<?php

namespace App\Http\Requests\Tenant\Cash\Cash;

use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use App\Models\Tenant\Cash\PettyCash;

class CashUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $clean = [];
        foreach ($this->all() as $key => $value) {
            if (is_string($key) && str_ends_with($key, '_edit')) {
                $clean[substr($key, 0, -5)] = $value; // quita "_edit"
            } else {
                $clean[$key] = $value;
            }
        }
        if (isset($clean['name'])) {
            $clean['name'] = is_string($clean['name']) ? trim($clean['name']) : $clean['name'];
        }
        $this->merge($clean);
    }

    public function rules(): array
    {
        $id     = $this->route('id');
        // sede INMUTABLE: la unicidad y el combo se validan contra la sede ACTUAL de la caja.
        $sedeId = optional(PettyCash::find($id))->sede_id;

        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('petty_cashes', 'name')
                    ->where(fn ($q) => $q->where('sede_id', $sedeId)->where('status', '<>', 'ANULADO'))
                    ->ignore($id),
            ],
            'vendedores'   => ['array'],
            'vendedores.*' => [Rule::exists('sede_user', 'user_id')->where('sede_id', $sedeId)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El campo nombre es obligatorio.',
            'name.unique'   => 'Ya existe una caja con ese nombre en esta sede.',
            'name.max'      => 'El nombre no debe exceder los 255 caracteres.',
            'vendedores.*.exists' => 'Un vendedor del combo no pertenece a la sede de la caja.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
