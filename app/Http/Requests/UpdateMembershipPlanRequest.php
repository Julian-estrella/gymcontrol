<?php

namespace App\Http\Requests;

use App\Models\MembershipPlan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMembershipPlanRequest extends FormRequest
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
        $plan = $this->route('membership_plan') ?? $this->route('membershipPlan');
        $planId = $plan instanceof MembershipPlan ? $plan->id : $plan;

        return [
            'name'          => ['required', 'string', 'min:3', 'max:100', Rule::unique('membership_plans')->ignore($planId)],
            'description'   => ['nullable', 'string', 'max:500'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:365'],
            'price'         => ['required', 'numeric', 'min:0', 'max:99999.99'],
            'is_active'     => ['sometimes', 'boolean'],
        ];
    }
}
