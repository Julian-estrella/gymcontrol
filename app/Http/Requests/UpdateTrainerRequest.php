<?php

namespace App\Http\Requests;

use App\Models\Trainer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTrainerRequest extends FormRequest
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
        $trainerId = $this->route('trainer') instanceof Trainer ? $this->route('trainer')->id : $this->route('trainer');

        return [
            'name'      => ['required', 'string', 'min:3', 'max:180'],
            'email'     => ['nullable', 'email', 'max:150', Rule::unique('trainers')->ignore($trainerId)],
            'phone'     => ['nullable', 'string', 'min:10', 'max:15'],
            'specialty' => ['required', 'string', 'max:120'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
