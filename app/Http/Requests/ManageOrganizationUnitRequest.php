<?php

namespace App\Http\Requests;

use App\Enums\OrganizationUnitType;
use App\Models\OrganizationUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ManageOrganizationUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage', OrganizationUnit::class) === true;
    }

    public function rules(): array
    {
        $organizationUnit = $this->route('organizationUnit');

        return [
            'code' => [
                'required',
                'string',
                'max:32',
                'alpha_dash:ascii',
                Rule::unique('organization_units', 'code')->ignore($organizationUnit),
            ],
            'name' => ['required', 'string', 'max:160'],
            'unit_type' => ['required', Rule::enum(OrganizationUnitType::class)],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('organization_units', 'id')->where('is_active', true),
                Rule::notIn([$organizationUnit instanceof OrganizationUnit ? $organizationUnit->id : null]),
            ],
            'description' => ['nullable', 'string', 'max:3000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
