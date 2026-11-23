<?php

namespace App\Http\Requests;

use App\Enums\RoleName;
use App\Models\ServiceCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCivicReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() === null
            || ($this->user()->isActive() && $this->user()->hasRole(RoleName::Citizen->value));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $category = ServiceCategory::query()->find($this->input('service_category_id'));
        $locationRule = $category?->requires_location ? ['required', 'numeric'] : ['nullable', 'numeric'];

        return [
            'service_category_id' => [
                'required',
                Rule::exists('service_categories', 'id')->where('is_active', true)->where('is_public', true),
            ],
            'title' => ['required', 'string', 'min:8', 'max:160'],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'latitude' => [...$locationRule, 'between:-90,90'],
            'longitude' => [...$locationRule, 'between:-180,180'],
            'address' => ['nullable', 'string', 'max:255'],
            'guest_email' => ['nullable', 'email:rfc', 'max:254'],
            'documents' => ['nullable', 'array', 'max:5'],
            'documents.*' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'extensions:jpg,jpeg,png,webp,pdf',
                'max:10240',
            ],
        ];
    }
}
