<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, string>  $input
     */
    public function update(User $user, array $input)
    {
        Validator::make($input, [
            'name' => ['nullable', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => ['nullable', 'regex:/^\+?[0-9]{10,15}$/'],
            'city' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
        ])->validateWithBag('updateProfileInformation');

        if (isset($input['email']) && $input['email'] !== $user->email) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $path = null;
            if (!empty($input['avatar']) && $input['avatar'] instanceof UploadedFile) {
                $path = $input['avatar']->store($user->id, 'public');
            }
            $user->forceFill([
                'name' => $input['name'] ?? null,
                'phone' => $input['phone'] ?? null,
                'city' => $input['city'] ?? null,
                'avatar' => $path,
            ])->save();
        }
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  array<string, string>  $input
     */
    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill([
            'email' => $input['email'],
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
