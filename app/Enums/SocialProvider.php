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

        if (! ($config['enabled'] ?? false) || blank($config['client_id'] ?? null) || blank($config['redirect'] ?? null)) {
            return false;
        }

        if ($this === self::Apple) {
            return filled($config['client_secret'] ?? null)
                || (filled($config['key_id'] ?? null)
                    && filled($config['team_id'] ?? null)
                    && filled($config['private_key'] ?? null));
        }

        return filled($config['client_secret'] ?? null);
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
}
