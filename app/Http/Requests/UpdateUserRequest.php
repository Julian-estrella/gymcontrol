<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
        $userId = $this->route('user') instanceof User ? $this->route('user')->id : $this->route('user');

        return [
            'name'      => ['required', 'string', 'min:3', 'max:100'],
            'email'     => ['required', 'email', 'max:150', Rule::unique('users')->ignore($userId)],
            'password'  => ['nullable', 'string', 'min:8', 'confirmed'],
            'role'      => ['required', Rule::exists('roles', 'slug')],
            'phone'     => ['nullable', 'string', 'max:15'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
