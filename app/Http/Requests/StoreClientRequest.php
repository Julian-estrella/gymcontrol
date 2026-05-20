<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
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
            'name'              => ['required', 'string', 'min:3', 'max:180'],
            'email'             => ['required', 'email', 'max:150', 'unique:clients,email'],
            'phone'             => ['required', 'string', 'min:10', 'max:15'],
            'user_id'           => ['nullable', 'exists:users,id'],
            'membership_status' => ['required', 'in:activo,vencido,sin_membresia'],
        ];
    }
}
