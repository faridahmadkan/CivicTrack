<?php

namespace App\Http\Requests\Admin;

use App\Enums\PermissionName;
use App\Enums\RoleName;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can(PermissionName::AccountsManage->value) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:254', Rule::unique('users', 'email')],
            'role' => ['required', Rule::in([
                RoleName::Citizen->value,
                RoleName::Staff->value,
                RoleName::Worker->value,
            ])],
            'organization_unit_id' => [
                Rule::requiredIf(fn (): bool => in_array($this->input('role'), [RoleName::Staff->value, RoleName::Worker->value], true)),
                'nullable',
                'integer',
                Rule::exists('organization_units', 'id')->where('is_active', true),
            ],
            'position_title' => [
                Rule::requiredIf(fn (): bool => in_array($this->input('role'), [RoleName::Staff->value, RoleName::Worker->value], true)),
                'nullable',
                'string',
                'max:120',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => mb_strtolower(trim((string) $this->input('email'))),
        ]);
    }
}
