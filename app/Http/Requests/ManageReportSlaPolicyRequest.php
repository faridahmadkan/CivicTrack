<?php

namespace App\Http\Requests;

use App\Enums\CivicReportPriority;
use App\Enums\PermissionName;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ManageReportSlaPolicyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can(PermissionName::SlaManage->value) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'service_category_id' => ['nullable', 'integer', Rule::exists('service_categories', 'id')],
            'organization_unit_id' => ['nullable', 'integer', Rule::exists('organization_units', 'id')],
            'priority' => ['nullable', Rule::in(array_map(fn (CivicReportPriority $priority): string => $priority->value, CivicReportPriority::cases()))],
            'first_response_minutes' => ['required', 'integer', 'min:1', 'max:525600'],
            'resolution_minutes' => ['required', 'integer', 'min:1', 'max:525600'],
            'escalation_repeat_minutes' => ['required', 'integer', 'min:1', 'max:525600'],
            'is_active' => ['required', 'boolean'],
            'effective_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->filled('first_response_minutes')
                && $this->filled('resolution_minutes')
                && (int) $this->input('resolution_minutes') < (int) $this->input('first_response_minutes')) {
                $validator->errors()->add('resolution_minutes', 'The resolution target must be at least as long as the first-response target.');
            }

            if ($this->filled('effective_at') && $this->filled('expires_at')
                && strtotime((string) $this->input('expires_at')) <= strtotime((string) $this->input('effective_at'))) {
                $validator->errors()->add('expires_at', 'The expiry time must be later than the effective time.');
            }
        });
    }
}
