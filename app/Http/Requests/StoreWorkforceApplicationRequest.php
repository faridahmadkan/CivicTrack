<?php

namespace App\Http\Requests;

use App\Enums\RoleName;
use App\Enums\WorkforceApplicationType;
use App\Models\WorkforceApplication;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreWorkforceApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $type = WorkforceApplicationType::tryFrom((string) $this->route('type'));
        $user = $this->user();

        return $type !== null
            && $user !== null
            && $user->isActive()
            && $user->hasRole(RoleName::Citizen->value)
            && ! $user->hasAnyRole([RoleName::Staff->value, RoleName::Worker->value])
            && $user->can('create', WorkforceApplication::class);
    }

    public function rules(): array
    {
        return [
            'position_title' => ['required', 'string', 'max:120'],
            'requested_unit_id' => [
                'required',
                'integer',
                Rule::exists('organization_units', 'id')->where('is_active', true),
            ],
            'experience_summary' => ['nullable', 'string', 'max:6000'],
            'qualification_summary' => ['nullable', 'string', 'max:6000'],
            'documents' => ['sometimes', 'array', 'max:5'],
            'documents.*' => [
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'extensions:pdf,jpg,jpeg,png',
                File::types(['pdf', 'jpg', 'jpeg', 'png'])->max(10240),
            ],
        ];
    }
}
