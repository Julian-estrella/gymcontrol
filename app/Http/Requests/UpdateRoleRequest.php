<?php

namespace App\Http\Requests;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canAccessModule('roles') ?? false;
    }

    public function rules(): array
    {
        $role = $this->route('role');
        $roleId = $role instanceof Role ? $role->id : $role;
        $modules = array_keys(config('gymcontrol_modules'));

        return [
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'slug' => ['nullable', 'string', 'max:100', 'alpha_dash', Rule::unique('roles', 'slug')->ignore($roleId)],
            'can_access_admin' => ['sometimes', 'boolean'],
            'modules' => ['nullable', 'array'],
            'modules.*' => ['string', Rule::in($modules)],
        ];
    }
}
