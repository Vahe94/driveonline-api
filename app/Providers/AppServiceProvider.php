<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $publicAppUrl = rtrim((string) config('app.url'), '/');
        if ($publicAppUrl !== '') {
            URL::forceRootUrl($publicAppUrl);

            $scheme = parse_url($publicAppUrl, PHP_URL_SCHEME);
            if (is_string($scheme) && $scheme !== '') {
                URL::forceScheme($scheme);
            }
        }

        VerifyEmail::createUrlUsing(function ($notifiable) {
            $frontendUrl = rtrim((string) config('app.frontend_url'), '/').'/email/verify';
            $signedUrl = URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60),
                [
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]
            );

            return $frontendUrl.'?url='.urlencode($signedUrl);
        });

        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new MailMessage)
                ->subject('Подтверждение адреса электронной почты')
                ->theme('driveonline')
                ->markdown('emails.verify-email', [
                    'actionUrl' => $url,
                ]);
        });

        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            $frontendUrl = rtrim((string) config('app.frontend_url'), '/').'/reset-password';

            return $frontendUrl.'?token='.urlencode($token).'&email='.urlencode($notifiable->email);
        });
    }
}
