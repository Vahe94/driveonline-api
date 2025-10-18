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
    public function update(User $user, array $input): void
    {
        $validated = Validator::make($input, [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => ['sometimes', 'regex:/^\+?[0-9]{10,15}$/'],
            'city' => ['sometimes', 'string', 'max:255'],
            'avatar' => ['sometimes', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
        ])->validateWithBag('updateProfileInformation');

        if (isset($validated['email']) && $validated['email'] !== $user->email) {
            $this->updateVerifiedUser($user, $input);
        } else {
            if (isset($validated['avatar'])) {
                $validated['avatar'] = $validated['avatar']->store($user->id, 'public');
            }

            $user->forceFill($validated)->save();
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
