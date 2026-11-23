<?php

namespace App\Http\Requests;

use App\Enums\PermissionName;
use App\Models\CivicReport;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CancelCivicReportRequest extends FormRequest
{
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

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return ['reason' => ['required', 'string', 'min:3', 'max:1000']];
    }
}
