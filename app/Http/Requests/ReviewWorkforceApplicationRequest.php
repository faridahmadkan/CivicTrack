<?php

namespace App\Http\Requests;

use App\Enums\WorkforceApplicationStatus;
use App\Models\WorkforceApplication;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewWorkforceApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $application = $this->route('workforceApplication');

        return $application instanceof WorkforceApplication
            && $this->user()?->can('review', $application) === true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::in([
                    WorkforceApplicationStatus::UnderReview->value,
                    WorkforceApplicationStatus::InformationRequested->value,
                    WorkforceApplicationStatus::Approved->value,
                    WorkforceApplicationStatus::Rejected->value,
                ]),
            ],
            'reason' => ['nullable', 'string', 'max:2000', 'required_if:status,information_requested,rejected'],
        ];
    }
}
