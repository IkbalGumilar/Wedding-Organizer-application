<?php

namespace App\Socialite;

use GuzzleHttp\RequestOptions;
use SocialiteProviders\Microsoft\Provider as BaseMicrosoftProvider;

class MicrosoftProvider extends BaseMicrosoftProvider
{
    /**
     * The maintained provider defaults to a broad Graph profile projection.
     * Authentication only needs the Graph subject and display name; the UPN is
     * retained only for the application's collision safeguard.
     *
     * @return array<string, mixed>
     */
    protected function getUserByToken($token)
    {
        $this->getClaims();

        $response = $this->getHttpClient()->get('https://graph.microsoft.com/v1.0/me', [
            RequestOptions::HEADERS => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$token,
            ],
            RequestOptions::QUERY => [
                '$select' => 'id,displayName,userPrincipalName',
            ],
            RequestOptions::PROXY => $this->getConfig('proxy'),
        ]);

        return json_decode((string) $response->getBody(), true);
    }
}
