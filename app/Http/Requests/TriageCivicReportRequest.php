<?php

namespace App\Http\Requests;

use App\Enums\CivicReportPriority;
use App\Enums\CivicReportStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TriageCivicReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('triage', $this->route('civicReport')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([
                CivicReportStatus::UnderReview->value,
                CivicReportStatus::Acknowledged->value,
                CivicReportStatus::NeedsInformation->value,
                CivicReportStatus::Rejected->value,
            ])],
            'priority' => ['required', Rule::enum(CivicReportPriority::class)],
            'reason' => ['nullable', 'string', 'max:1000', 'required_if:status,needs_information,rejected'],
            'is_public' => ['sometimes', 'boolean'],
        ];
    }
}
