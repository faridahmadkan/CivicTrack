<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Services\CitizenRegistrationService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function __construct(private readonly CitizenRegistrationService $registration) {}

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:120'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'phone' => ['nullable', 'string', 'max:32', 'regex:/^[0-9+().\-\s]{7,32}$/'],
            'age' => ['required', 'integer', 'min:13', 'max:120'],
            'locality' => ['required', 'string', 'min:2', 'max:120'],
            'home_latitude' => ['nullable', 'required_with:home_longitude', 'numeric', 'between:-90,90'],
            'home_longitude' => ['nullable', 'required_with:home_latitude', 'numeric', 'between:-180,180'],
            'password' => $this->passwordRules(),
            'privacy_acknowledged' => ['accepted'],
        ])->validate();

        return $this->registration->register([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
            'phone' => $input['phone'] ?? null,
            'age' => $input['age'],
            'locality' => $input['locality'],
            'home_latitude' => $input['home_latitude'] ?? null,
            'home_longitude' => $input['home_longitude'] ?? null,
        ]);
    }
}
