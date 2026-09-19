<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::authenticateUsing(function (Request $request): ?User {
            $email = Str::lower(trim((string) $request->input('email')));
            $user = User::query()->where('email', $email)->first();

            if (! $user || ! $user->hasPassword()) {
                return null;
            }

            return Hash::check((string) $request->input('password'), (string) $user->password) ? $user : null;
        });
        Fortify::loginView(fn () => view('auth.login'));
        Fortify::registerView(fn () => view('auth.register'));
        Fortify::requestPasswordResetLinkView(fn () => view('auth.forgot-password'));
        Fortify::resetPasswordView(fn ($request) => view('auth.reset-password', ['request' => $request]));
        Fortify::confirmPasswordView(fn () => view('auth.confirm-password'));

        RateLimiter::for('login', function (Request $request): Limit {
            return Limit::perMinute(5)->by(
                Str::transliterate(Str::lower((string) $request->input('email')).'|'.$request->ip()),
            );
        });
    }
}
