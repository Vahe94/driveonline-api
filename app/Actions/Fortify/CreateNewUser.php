<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    private const DISALLOWED_EMAIL_DOMAINS = [
        'gmail.com',
        'googlemail.com',
    ];

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
                function (string $attribute, mixed $value, Closure $fail): void {
                    $domain = strtolower(substr(strrchr((string) $value, '@') ?: '', 1));

                    if (in_array($domain, self::DISALLOWED_EMAIL_DOMAINS, true)) {
                        $fail('Регистрация с Gmail недоступна. Используйте другой e-mail.');
                    }
                },
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
