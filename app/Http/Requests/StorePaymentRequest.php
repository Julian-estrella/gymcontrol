<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
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
            'client_id'            => ['required', 'exists:clients,id'],
            'client_membership_id' => ['nullable', 'exists:client_memberships,id'],
            'membership_plan_id'   => ['required', 'exists:membership_plans,id'],
            'amount'               => ['required', 'numeric', 'min:1', 'max:99999.99'],
            'payment_method'       => ['required', 'in:cash,card,transfer,other'],
            'payment_date'         => ['sometimes', 'date', 'before_or_equal:today'],
            'status'               => ['required', 'in:paid,pending,cancelled'],
            'notes'                => ['nullable', 'string', 'max:500'],
        ];
    }
}
