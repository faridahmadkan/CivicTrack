<?php

namespace App\Http\Requests;

use App\Enums\WorkforceApplicationType;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Rules\Password;

class StoreWorkforceApplicantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() === null
            && WorkforceApplicationType::tryFrom((string) $this->route('type')) !== null;
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
            'email' => ['required', 'string', 'email:rfc', 'max:255', Rule::unique(User::class)],
            'phone' => ['nullable', 'string', 'max:32', 'regex:/^[0-9+().\-\s]{7,32}$/'],
            'age' => ['required', 'integer', 'min:13', 'max:120'],
            'locality' => ['required', 'string', 'min:2', 'max:120'],
            'home_latitude' => ['nullable', 'required_with:home_longitude', 'numeric', 'between:-90,90'],
            'home_longitude' => ['nullable', 'required_with:home_latitude', 'numeric', 'between:-180,180'],
            'password' => ['required', 'string', Password::default(), 'confirmed'],
            'privacy_acknowledged' => ['accepted'],
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
