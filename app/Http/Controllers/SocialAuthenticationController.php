<?php

namespace App\Http\Controllers;

use App\Actions\Authentication\ResolveSocialAccount;
use App\Enums\SocialProvider;
use App\Models\User;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Laravel\Socialite\Contracts\User as ProviderUser;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Symfony\Component\HttpFoundation\Cookie as HttpCookie;
use Throwable;

class SocialAuthenticationController extends Controller
{
    private const APPLE_LINK_INTENT_COOKIE = 'atha_social_apple_link_intent';

    private const APPLE_LINK_PENDING_SESSION = 'social.apple.link.pending';

    public function redirect(Request $request, string $provider): RedirectResponse
    {
        $socialProvider = $this->provider($provider);
        $linkingUser = $request->user();

        abort_if($linkingUser?->is_admin, 403);
        abort_if($linkingUser && ! $socialProvider->canLink(), 404);

        if ($socialProvider->usesCookieNonce()) {
            if (! $linkingUser) {
                return $this->driver($socialProvider)->stateless()->cookieNonce()->redirect();
            }

            $intent = Str::random(64);
            Cache::put($this->appleLinkIntentKey($intent), [
                'user_id' => $linkingUser->id,
                'session_token_hash' => $this->sessionTokenHash($request),
            ], now()->addMinutes(10));

            return $this->driver($socialProvider)
                ->redirectUrl($socialProvider->linkRedirect())
                ->stateless()
                ->cookieNonce()
                ->redirect()
                ->withCookie($this->appleLinkIntentCookie($intent));
        }

        if ($linkingUser) {
            $request->session()->put($this->linkingSessionKey($socialProvider), $linkingUser->id);
        } else {
            $request->session()->forget($this->linkingSessionKey($socialProvider));
        }

        return $this->driver($socialProvider)->redirect();
    }

    public function callback(Request $request, string $provider, ResolveSocialAccount $resolveSocialAccount): RedirectResponse
    {
        return $this->handleCallback($request, $this->provider($provider), $resolveSocialAccount);
    }

    public function appleLinkCallback(Request $request, ResolveSocialAccount $resolveSocialAccount): RedirectResponse
    {
        $provider = $this->provider(SocialProvider::Apple->value);
        abort_unless($provider->canLink(), 404);

        return $this->handleCallback($request, $provider, $resolveSocialAccount, true);
    }

    public function showAppleLinkCompletion(Request $request, string $token): View|RedirectResponse
    {
        $pending = Cache::pull($this->appleLinkCompletionKey($token));

        if (! is_array($pending)
            || ($pending['user_id'] ?? null) !== $request->user()->id
            || ! is_string($pending['session_token_hash'] ?? null)
            || ! hash_equals($pending['session_token_hash'], $this->sessionTokenHash($request))
            || blank($pending['provider_user_id'] ?? null)) {
            return redirect()->route('profile.edit')->withErrors([
                'social' => 'Permintaan menghubungkan akun Apple sudah kedaluwarsa. Silakan coba lagi.',
            ]);
        }

        $request->session()->put(self::APPLE_LINK_PENDING_SESSION, [
            'provider_user_id' => $pending['provider_user_id'],
        ]);

        return view('auth.apple-link-confirmation');
    }

    public function confirmAppleLinkCompletion(Request $request, ResolveSocialAccount $resolveSocialAccount): RedirectResponse
    {
        $pending = $request->session()->pull(self::APPLE_LINK_PENDING_SESSION);

        if (! is_array($pending) || blank($pending['provider_user_id'] ?? null)) {
            return redirect()->route('profile.edit')->withErrors([
                'social' => 'Permintaan menghubungkan akun Apple sudah kedaluwarsa. Silakan coba lagi.',
            ]);
        }

        try {
            $resolveSocialAccount->link(SocialProvider::Apple, $request->user(), $pending['provider_user_id']);
        } catch (DomainException $exception) {
            return redirect()->route('profile.edit')->withErrors(['social' => $exception->getMessage()]);
        }

        return redirect()->route('profile.edit')->with('status', 'Akun Apple berhasil dihubungkan.');
    }

    public function skipProfileCompletion(Request $request): RedirectResponse
    {
        $request->session()->forget('social.profile.prompt');

        return redirect()->route('dashboard');
    }

    private function handleCallback(
        Request $request,
        SocialProvider $socialProvider,
        ResolveSocialAccount $resolveSocialAccount,
        bool $requiresAppleLink = false,
    ): RedirectResponse {
        $linkingUser = null;

        try {
            $providerUser = $this->providerUser($socialProvider);

            if ($requiresAppleLink) {
                return $this->beginAppleLinkCompletion($request, $socialProvider, $providerUser);
            }

            $linkingUser = $socialProvider->usesCookieNonce()
                ? null
                : $this->pullSessionLinkingUser($request, $socialProvider);

            $user = $resolveSocialAccount->handle($socialProvider, $providerUser, $linkingUser);

            if ($user->is_admin) {
                throw new DomainException('Social login hanya tersedia untuk akun pelanggan.');
            }

            Auth::login($user);
            $request->session()->regenerate();

            if ($linkingUser) {
                return redirect()->route('profile.edit')->with('status', 'Akun '.$socialProvider->label().' berhasil dihubungkan.');
            }

            if (blank($user->phone)) {
                $request->session()->put('social.profile.prompt', true);

                return redirect()->route('profile.edit');
            }

            return redirect()->intended(route('dashboard'));
        } catch (InvalidStateException) {
            return $this->callbackFailure($request, $socialProvider, 'Permintaan masuk sosial sudah kedaluwarsa. Silakan coba lagi.', $requiresAppleLink, $linkingUser !== null);
        } catch (DomainException $exception) {
            return $this->callbackFailure($request, $socialProvider, $exception->getMessage(), $requiresAppleLink, $linkingUser !== null);
        } catch (Throwable $exception) {
            report($exception);

            return $this->callbackFailure($request, $socialProvider, 'Masuk dengan '.$socialProvider->label().' belum dapat diproses. Silakan coba lagi.', $requiresAppleLink, $linkingUser !== null);
        }
    }

    private function beginAppleLinkCompletion(Request $request, SocialProvider $provider, ProviderUser $providerUser): RedirectResponse
    {
        $intent = $request->cookie(self::APPLE_LINK_INTENT_COOKIE);
        $payload = is_string($intent) ? Cache::pull($this->appleLinkIntentKey($intent)) : null;
        $providerUserId = trim((string) $providerUser->getId());

        if (! is_array($payload)
            || ! is_int($payload['user_id'] ?? null)
            || ! is_string($payload['session_token_hash'] ?? null)
            || $providerUserId === '') {
            throw new DomainException('Permintaan menghubungkan akun Apple sudah kedaluwarsa. Silakan coba lagi.');
        }

        $completion = Str::random(64);
        Cache::put($this->appleLinkCompletionKey($completion), [
            'user_id' => $payload['user_id'],
            'session_token_hash' => $payload['session_token_hash'],
            'provider_user_id' => $providerUserId,
        ], now()->addMinutes(5));

        return $this->callbackResponse(
            redirect()->route('social.apple-link.complete', ['token' => $completion]),
            $provider,
            true,
        );
    }

    private function provider(string $provider): SocialProvider
    {
        $socialProvider = SocialProvider::tryFrom($provider);

        abort_unless($socialProvider?->isEnabled(), 404);

        return $socialProvider;
    }

    private function driver(SocialProvider $provider): object
    {
        $driver = Socialite::driver($provider->value);

        if ($provider === SocialProvider::Microsoft) {
            $driver->setScopes(['openid', 'profile', 'User.Read']);
        }

        if ($provider === SocialProvider::Facebook) {
            $driver->setScopes(['email']);
            $driver->fields(['id', 'name', 'email']);
        }

        if ($provider === SocialProvider::X) {
            $driver->setScopes(['users.read']);
        }

        return $driver;
    }

    private function providerUser(SocialProvider $provider): ProviderUser
    {
        $driver = $this->driver($provider);

        if ($provider->usesCookieNonce()) {
            $driver = $driver->stateless()->cookieNonce();
        }

        return $driver->user();
    }

    private function pullSessionLinkingUser(Request $request, SocialProvider $provider): ?User
    {
        $userId = $request->session()->pull($this->linkingSessionKey($provider));

        if (! $userId) {
            return null;
        }

        $user = $request->user();

        if (! $user || $user->id !== (int) $userId || $user->is_admin) {
            throw new DomainException('Sesi untuk menghubungkan akun sudah berakhir. Silakan masuk kembali dan coba lagi.');
        }

        return $user;
    }

    private function callbackFailure(
        Request $request,
        SocialProvider $provider,
        string $message,
        bool $requiresAppleLink,
        bool $wasLinking,
    ): RedirectResponse {
        $response = $requiresAppleLink || ($wasLinking && $request->user())
            ? redirect()->route('profile.edit')
            : redirect()->route('login');

        return $this->callbackResponse($response->withErrors(['social' => $message]), $provider, $requiresAppleLink);
    }

    private function callbackResponse(RedirectResponse $response, SocialProvider $provider, bool $clearAppleLinkIntent = false): RedirectResponse
    {
        if (! $provider->usesCookieNonce() || ! $clearAppleLinkIntent) {
            return $response;
        }

        return $response->withCookie($this->expiredAppleLinkIntentCookie());
    }

    private function appleLinkIntentKey(string $intent): string
    {
        return 'social-login:apple-link-intent:'.hash('sha256', $intent);
    }

    private function appleLinkCompletionKey(string $completion): string
    {
        return 'social-login:apple-link-completion:'.hash('sha256', $completion);
    }

    private function linkingSessionKey(SocialProvider $provider): string
    {
        return 'social.linking.'.$provider->value;
    }

    private function sessionTokenHash(Request $request): string
    {
        return hash('sha256', $request->session()->token());
    }

    private function appleLinkIntentCookie(string $intent): HttpCookie
    {
        return Cookie::make(
            self::APPLE_LINK_INTENT_COOKIE,
            $intent,
            10,
            '/',
            config('session.domain'),
            true,
            true,
            false,
            'none',
        );
    }

    private function expiredAppleLinkIntentCookie(): HttpCookie
    {
        return Cookie::make(
            self::APPLE_LINK_INTENT_COOKIE,
            '',
            -1,
            '/',
            config('session.domain'),
            true,
            true,
            false,
            'none',
        );
    }
}
