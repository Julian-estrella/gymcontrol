<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGymClassRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->schedule)) {
            $decoded = json_decode($this->schedule, true);
            if (is_array($decoded)) {
                $this->merge([
                    'schedule' => $decoded,
                ]);
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'             => ['required', 'string', 'min:3', 'max:120'],
            'description'      => ['nullable', 'string', 'max:500'],
            'trainer_id'       => ['required', 'exists:trainers,id'],
            'schedule'         => ['required', 'array', 'min:1'],
            'schedule.*.day'   => ['required', 'string', 'in:Lunes,Martes,Miércoles,Jueves,Viernes,Sábado,Domingo'],
            'schedule.*.start' => ['required', 'date_format:H:i'],
            'schedule.*.end'   => ['required', 'date_format:H:i', 'after:schedule.*.start'],
            'max_capacity'     => ['required', 'integer', 'min:1', 'max:100'],
            'is_active'        => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'schedule.*.day'   => 'día de la semana',
            'schedule.*.start' => 'hora de inicio',
            'schedule.*.end'   => 'hora de fin',
        ];
    }
}
