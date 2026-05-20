<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMembershipPlanRequest extends FormRequest
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
            'name'          => ['required', 'string', 'min:3', 'max:100', 'unique:membership_plans,name'],
            'description'   => ['nullable', 'string', 'max:500'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:365'],
            'price'         => ['required', 'numeric', 'min:0', 'max:99999.99'],
            'is_active'     => ['sometimes', 'boolean'],
        ];
    }
}
