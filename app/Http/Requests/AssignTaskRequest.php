<?php

namespace App\Http\Requests;

use App\Models\WorkTask;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $task = $this->route('workTask');

        return $task instanceof WorkTask && ($this->user()?->can('assign', $task) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'worker_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'assignment_instructions' => ['nullable', 'string', 'max:2000'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
