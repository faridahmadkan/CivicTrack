<?php

namespace App\Http\Requests;

use App\Enums\CivicReportPriority;
use App\Models\CivicReport;
use App\Models\WorkOrder;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateWorkOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $report = $this->route('report');

        return $report instanceof CivicReport && ($this->user()?->can('create', [WorkOrder::class, $report]) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:160'],
            'instructions' => ['nullable', 'string', 'max:4000'],
            'due_at' => ['nullable', 'date', 'after:now'],
            'tasks' => ['required', 'array', 'min:1', 'max:25'],
            'tasks.*.title' => ['required', 'string', 'min:4', 'max:160'],
            'tasks.*.instructions' => ['nullable', 'string', 'max:3000'],
            'tasks.*.priority' => ['required', Rule::enum(CivicReportPriority::class)],
            'tasks.*.due_at' => ['nullable', 'date', 'after:now'],
            'tasks.*.depends_on' => ['nullable', 'array', 'max:24'],
            'tasks.*.depends_on.*' => ['required', 'integer', 'min:0'],
        ];
    }
}
