<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

class PlaceSearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'q' => [
                'required',
                'string',
                'min:2',
                'max:120',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (is_string($value)
                        && preg_match('/^-?\d{1,2}\.\d+\s*[, ]\s*-?\d{1,3}\.\d+$/', trim($value)) === 1) {
                        $fail('Search for a place name instead of sending coordinate values.');
                    }
                },
            ],
        ];
    }
}
