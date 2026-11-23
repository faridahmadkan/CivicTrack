<?php

namespace App\Http\Requests;

use App\Enums\RoleName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ManageRegistrationControlsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('registrations.manage') === true;
    }

    public function rules(): array
    {
        return [
            'context' => ['required', Rule::in([
                RoleName::Citizen->value,
                RoleName::Staff->value,
                RoleName::Worker->value,
            ])],
            'enabled' => ['required', 'boolean'],
            'opens_at' => ['nullable', 'date'],
            'closes_at' => ['nullable', 'date', 'after:opens_at'],
        ];
    }
}
