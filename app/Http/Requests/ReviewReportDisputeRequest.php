<?php

namespace App\Http\Requests;

use App\Models\CivicReport;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ReviewReportDisputeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $report = $this->route('civicReport');

        return $report instanceof CivicReport && ($this->user()?->can('triage', $report) ?? false);
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return ['decision' => ['required', 'in:reopen,uphold'], 'reason' => ['required', 'string', 'min:3', 'max:1000']];
    }
}
