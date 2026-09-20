<?php

namespace App\Enums;

enum SocialProvider: string
{
    case Google = 'google';
    case Apple = 'apple';
    case Microsoft = 'microsoft';
    case Facebook = 'facebook';
    case X = 'x';

    public function label(): string
    {
        return match ($this) {
            self::Google => 'Google',
            self::Apple => 'Apple',
            self::Microsoft => 'Microsoft',
            self::Facebook => 'Facebook',
            self::X => 'X',
        };
    }

    public function isEnabled(): bool
    {
        $config = config('services.'.$this->value, []);
        $clientId = trim((string) ($config['client_id'] ?? ''));
        $redirect = trim((string) ($config['redirect'] ?? ''));

        if (! ($config['enabled'] ?? false)
            || ! $this->hasConfiguredValue($clientId)
            || ! $this->hasConfiguredValue($redirect)) {
            return false;
        }

        if ($this === self::Google && ! preg_match('/^[A-Za-z0-9._-]+\.apps\.googleusercontent\.com$/', $clientId)) {
            return false;
        }

        if ($this === self::Apple) {
            return $this->hasConfiguredValue($config['client_secret'] ?? null)
                || ($this->hasConfiguredValue($config['key_id'] ?? null)
                    && $this->hasConfiguredValue($config['team_id'] ?? null)
                    && $this->hasConfiguredValue($config['private_key'] ?? null));
        }

        return $this->hasConfiguredValue($config['client_secret'] ?? null);
    }

    public function usesCookieNonce(): bool
    {
        return $this === self::Apple;
    }

    public function canLink(): bool
    {
        return $this !== self::Apple || filled(config('services.apple.link_redirect'));
    }

    public function linkRedirect(): string
    {
        return (string) config('services.apple.link_redirect');
    }

    /** @return list<self> */
    public static function enabled(): array
    {
        return array_values(array_filter(self::cases(), fn (self $provider): bool => $provider->isEnabled()));
    }

    private function hasConfiguredValue(mixed $value): bool
    {
        $value = strtolower(trim((string) $value));

        return $value !== '' && ! in_array($value, [
            '...',
            'changeme',
            'replace-me',
            'client-id-asli.apps.googleusercontent.com',
            'client-secret-asli',
            'your-client-id',
            'your-client-secret',
        ], true);
    }
}
