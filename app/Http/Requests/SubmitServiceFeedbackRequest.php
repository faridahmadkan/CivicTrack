<?php

namespace App\Http\Requests;

use App\Enums\PermissionName;
use App\Models\CivicReport;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SubmitServiceFeedbackRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $report = $this->route('report');

        if (! $report instanceof CivicReport) {
            return false;
        }

        if ($this->user() !== null) {
            return $this->user()->can(PermissionName::FeedbackCreateOwn->value)
                && $report->isOwnedBy($this->user());
        }

        return $this->session()->get('guest_report_access_'.$report->id) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
