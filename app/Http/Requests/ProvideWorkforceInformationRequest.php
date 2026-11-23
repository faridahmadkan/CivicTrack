<?php

namespace App\Http\Requests;

use App\Enums\WorkforceApplicationStatus;
use App\Models\WorkforceApplication;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class ProvideWorkforceInformationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $application = $this->route('workforceApplication');

        abort_unless($application instanceof WorkforceApplication && $application->isOwnedBy($this->user()), 404);

        return $application->status === WorkforceApplicationStatus::InformationRequested;
    }

    public function rules(): array
    {
        return [
            'additional_information' => ['required', 'string', 'max:4000'],
            'documents' => ['sometimes', 'array', 'max:5'],
            'documents.*' => ['file', File::types(['pdf', 'jpg', 'jpeg', 'png'])->max(10240)],
        ];
    }
}
