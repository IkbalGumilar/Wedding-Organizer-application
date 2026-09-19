<?php

namespace App\Actions\Authentication;

use App\Enums\SocialProvider;
use App\Models\SocialAccount;
use App\Models\User;
use DomainException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as ProviderUser;

class ResolveSocialAccount
{
    public function handle(SocialProvider $provider, ProviderUser $providerUser, ?User $linkingUser = null): User
    {
        $providerUserId = trim((string) $providerUser->getId());

        if ($providerUserId === '') {
            throw new DomainException('Identitas akun dari provider tidak tersedia. Silakan coba lagi.');
        }

        if ($linkingUser) {
            return $this->link($provider, $linkingUser, $providerUserId);
        }

        $observedEmail = $this->normalizedEmail($providerUser->getEmail());
        $verifiedEmail = $this->verifiedEmail($provider, $providerUser);

        try {
            return DB::transaction(function () use ($provider, $providerUser, $providerUserId, $observedEmail, $verifiedEmail): User {
                $existingAccount = SocialAccount::query()
                    ->with('user')
                    ->where('provider', $provider->value)
                    ->where('provider_user_id', $providerUserId)
                    ->lockForUpdate()
                    ->first();

                if ($existingAccount) {
                    return $existingAccount->user;
                }

                if ($observedEmail && User::query()->where('email', $observedEmail)->exists()) {
                    throw new DomainException('Email ini sudah terdaftar. Masuk dengan email dan password terlebih dahulu, lalu hubungkan provider dari profil.');
                }

                $user = new User([
                    'name' => $this->nameFor($provider, $providerUser),
                    'email' => $verifiedEmail,
                    'password' => null,
                ]);
                $user->email_verified_at = $verifiedEmail ? now() : null;
                $user->save();

                return $this->attach($user, $provider, $providerUserId);
            }, attempts: 3);
        } catch (UniqueConstraintViolationException $exception) {
            $account = SocialAccount::query()
                ->with('user')
                ->where('provider', $provider->value)
                ->where('provider_user_id', $providerUserId)
                ->first();

            if ($account) {
                return $account->user;
            }

            if ($observedEmail && User::query()->where('email', $observedEmail)->exists()) {
                throw new DomainException('Email ini sudah terdaftar. Masuk dengan email dan password terlebih dahulu, lalu hubungkan provider dari profil.');
            }

            throw $exception;
        }
    }

    public function link(SocialProvider $provider, User $user, string $providerUserId): User
    {
        $providerUserId = trim($providerUserId);

        if ($providerUserId === '') {
            throw new DomainException('Identitas akun dari provider tidak tersedia. Silakan coba lagi.');
        }

        if ($user->is_admin) {
            throw new DomainException('Akun admin tidak dapat dihubungkan dengan provider sosial.');
        }

        try {
            return DB::transaction(function () use ($provider, $user, $providerUserId): User {
                $existingAccount = SocialAccount::query()
                    ->where('provider', $provider->value)
                    ->where('provider_user_id', $providerUserId)
                    ->lockForUpdate()
                    ->first();

                if ($existingAccount && $existingAccount->user_id !== $user->id) {
                    throw new DomainException('Akun '.$provider->label().' tersebut sudah terhubung ke akun lain.');
                }

                return $this->attach($user, $provider, $providerUserId);
            }, attempts: 3);
        } catch (UniqueConstraintViolationException $exception) {
            $account = SocialAccount::query()
                ->where('provider', $provider->value)
                ->where('provider_user_id', $providerUserId)
                ->first();

            if ($account) {
                if ($account->user_id !== $user->id) {
                    throw new DomainException('Akun '.$provider->label().' tersebut sudah terhubung ke akun lain.');
                }

                return $user;
            }

            throw $exception;
        }
    }

    private function attach(User $user, SocialProvider $provider, string $providerUserId): User
    {
        $existingProvider = SocialAccount::query()
            ->where('user_id', $user->id)
            ->where('provider', $provider->value)
            ->lockForUpdate()
            ->first();

        if ($existingProvider && $existingProvider->provider_user_id !== $providerUserId) {
            throw new DomainException('Akun '.$provider->label().' lain sudah terhubung ke profil ini.');
        }

        if (! $existingProvider) {
            $user->socialAccounts()->create([
                'provider' => $provider->value,
                'provider_user_id' => $providerUserId,
            ]);
        }

        return $user;
    }

    private function normalizedEmail(?string $email): ?string
    {
        $email = filled($email) ? Str::lower(trim($email)) : null;

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    private function verifiedEmail(SocialProvider $provider, ProviderUser $providerUser): ?string
    {
        $email = $this->normalizedEmail($providerUser->getEmail());

        if (! $email) {
            return null;
        }

        $claims = $providerUser->getRaw();

        // The provider subject is the login identity. Only claims that explicitly
        // mark an email as verified may become the account's local email address.
        return match ($provider) {
            SocialProvider::Google, SocialProvider::Apple => $this->isVerified(
                $claims['email_verified'] ?? $claims['verified_email'] ?? null,
            ) ? $email : null,
            default => null,
        };
    }

    private function isVerified(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === true;
    }

    private function nameFor(SocialProvider $provider, ProviderUser $providerUser): string
    {
        $name = trim((string) ($providerUser->getName() ?: $providerUser->getNickname()));

        return $name === '' ? 'Pelanggan '.$provider->label() : Str::limit($name, 150, '');
    }
}
