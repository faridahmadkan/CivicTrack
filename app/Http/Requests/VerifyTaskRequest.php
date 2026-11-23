<?php

namespace App\Http\Requests;

use App\Enums\TaskVerificationDecision;
use App\Models\WorkTask;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VerifyTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $task = $this->route('workTask');

        return $task instanceof WorkTask && ($this->user()?->can('verify', $task) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::enum(TaskVerificationDecision::class)],
            'reason' => ['nullable', 'string', 'max:1500', 'required_if:decision,needs_rework'],
        ];
    }
}
