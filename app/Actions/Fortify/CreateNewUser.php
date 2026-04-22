<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {        
        Validator::make($input, [
            'name' => ['nullable', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'privacy_policy_accepted' => ['accepted'],
            'terms_accepted' => ['accepted'],
            'phone' => ['required', 'regex:/^\+?[0-9]{10,15}$/'],
            'city' => ['required', 'string', 'max:255'],
            'password' => $this->passwordRules(),
        ])->validate();

        return User::create([
            'name' => $input['name'] ?? null,
            'email' => $input['email'],
            'phone' => $input['phone'],
            'city' => $input['city'],
            'password' => Hash::make($input['password']),
            'marketing_consent' => $input['marketing_consent'] === true,
        ]);
    }
}
