<?php

namespace Tests\Feature;

use App\Models\SocialAccount;
use App\Models\User;
use App\Models\WeddingPackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class SocialAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['google', 'apple', 'microsoft', 'facebook', 'x'] as $provider) {
            config()->set("services.{$provider}.enabled", true);
            config()->set("services.{$provider}.client_id", 'test-client-id');
            config()->set("services.{$provider}.client_secret", 'test-client-secret');
            config()->set("services.{$provider}.redirect", 'https://example.test/auth/'.$provider.'/callback');
        }

        config()->set('services.apple.link_redirect', 'https://example.test/auth/apple/link/callback');
    }

    public function test_each_enabled_provider_can_create_a_passwordless_customer_account(): void
    {
        foreach (['google', 'apple', 'microsoft', 'facebook', 'x'] as $provider) {
            $this->fakeSocialUser($provider, [
                'id' => $provider.'-customer-1',
                'name' => ucfirst($provider).' Customer',
                'email' => $provider.'@example.test',
            ]);

            $this->get(route('social.callback', $provider))
                ->assertRedirect(route('profile.edit'));

            $user = SocialAccount::query()
                ->where('provider', $provider)
                ->where('provider_user_id', $provider.'-customer-1')
                ->firstOrFail()
                ->user;

            $this->assertAuthenticatedAs($user);
            $this->assertNull($user->password);
            $this->assertSame(
                in_array($provider, ['google', 'apple'], true) ? $provider.'@example.test' : null,
                $user->email,
            );
            $this->assertSame(in_array($provider, ['google', 'apple'], true), $user->email_verified_at !== null);
            $this->assertFalse($user->is_admin);
            $this->assertDatabaseHas('social_accounts', [
                'user_id' => $user->id,
                'provider' => $provider,
                'provider_user_id' => $provider.'-customer-1',
            ]);

            $this->post(route('logout'));
        }
    }

    public function test_each_provider_reuses_the_same_account_on_a_second_login(): void
    {
        $expectedUserCount = 0;

        foreach (['google', 'apple', 'microsoft', 'facebook', 'x'] as $provider) {
            $providerUserId = $provider.'-repeat-customer';
            $email = $provider.'-repeat@example.test';

            $this->fakeSocialUser($provider, [
                'id' => $providerUserId,
                'name' => ucfirst($provider).' Repeat Customer',
                'email' => $email,
            ]);

            $this->get(route('social.callback', $provider))->assertRedirect(route('profile.edit'));
            $user = SocialAccount::query()->where('provider', $provider)->where('provider_user_id', $providerUserId)->sole()->user;
            $user->update(['phone' => '628123456789']);
            $this->post(route('logout'))->assertRedirect(route('home'));

            $this->fakeSocialUser($provider, [
                'id' => $providerUserId,
                'name' => 'Updated Provider Name',
                'email' => $email,
            ]);

            $this->get(route('social.callback', $provider))->assertRedirect(route('dashboard'));
            $this->assertAuthenticatedAs($user);

            $expectedUserCount++;
            $this->assertDatabaseCount('users', $expectedUserCount);
            $this->assertDatabaseCount('social_accounts', $expectedUserCount);

            $this->post(route('logout'));
        }
    }

    public function test_enabled_providers_render_accessible_controls_on_login_and_registration(): void
    {
        $login = $this->withoutVite()->get(route('login'))->assertOk();
        $registration = $this->get(route('register'))->assertOk();

        foreach (['google', 'apple', 'microsoft', 'facebook', 'x'] as $provider) {
            $label = 'Lanjutkan dengan '.match ($provider) {
                'x' => 'X',
                default => ucfirst($provider),
            };

            $login
                ->assertSee('data-social-provider="'.$provider.'"', false)
                ->assertSee('aria-label="'.$label.'"', false);
            $registration->assertSee('data-social-provider="'.$provider.'"', false);
        }
    }

    public function test_an_unverified_social_email_is_not_used_as_a_local_login_email(): void
    {
        $this->fakeSocialUser('google', [
            'id' => 'google-customer-1',
            'email' => 'unverified@example.test',
            'email_verified' => false,
        ]);

        $this->get(route('social.callback', 'google'))->assertRedirect(route('profile.edit'));

        $user = User::query()->sole();

        $this->assertNull($user->email);
        $this->assertNull($user->email_verified_at);
        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_user_id' => 'google-customer-1',
        ]);
    }

    public function test_an_observed_untrusted_social_email_cannot_create_a_duplicate_or_auto_link_an_existing_account(): void
    {
        $existingUser = User::factory()->create(['email' => 'customer@example.test']);

        $this->fakeSocialUser('microsoft', [
            'id' => 'microsoft-customer-1',
            'email' => $existingUser->email,
        ]);

        $this->get(route('social.callback', 'microsoft'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('social');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('social_accounts', 0);
    }

    public function test_existing_social_account_logs_in_without_creating_a_duplicate_user(): void
    {
        $user = User::factory()->create(['phone' => '628123456789']);
        SocialAccount::factory()->for($user)->create([
            'provider' => 'google',
            'provider_user_id' => 'google-customer-1',
        ]);

        $this->fakeSocialUser('google', [
            'id' => 'google-customer-1',
            'email' => 'new-email@example.test',
        ]);

        $this->get(route('social.callback', 'google'))
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('social_accounts', 1);
    }

    public function test_guest_social_callback_never_auto_links_an_existing_email_account(): void
    {
        $existingUser = User::factory()->create(['email' => 'customer@example.test']);

        $this->fakeSocialUser('google', [
            'id' => 'google-customer-1',
            'email' => $existingUser->email,
        ]);

        $this->get(route('social.callback', 'google'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('social');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('social_accounts', 0);
    }

    public function test_authenticated_customer_can_explicitly_link_an_unused_provider(): void
    {
        $customer = User::factory()->create(['email' => 'customer@example.test']);
        $this->actingAs($customer);

        $this->fakeSocialUser('microsoft', [
            'id' => 'microsoft-customer-1',
            'email' => $customer->email,
        ]);

        $this->get(route('social.redirect', 'microsoft'))
            ->assertRedirect('https://socialite.fake/microsoft/authorize');

        $this->get(route('social.callback', 'microsoft'))
            ->assertRedirect(route('profile.edit'));

        $this->assertAuthenticatedAs($customer);
        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $customer->id,
            'provider' => 'microsoft',
            'provider_user_id' => 'microsoft-customer-1',
        ]);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_authenticated_customer_cannot_claim_a_provider_already_linked_to_another_user(): void
    {
        $owner = User::factory()->create();
        $customer = User::factory()->create();
        SocialAccount::factory()->for($owner)->create([
            'provider' => 'facebook',
            'provider_user_id' => 'facebook-customer-1',
        ]);

        $this->actingAs($customer);
        $this->fakeSocialUser('facebook', ['id' => 'facebook-customer-1']);

        $this->get(route('social.redirect', 'facebook'));
        $this->get(route('social.callback', 'facebook'))
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHasErrors('social');

        $this->assertAuthenticatedAs($customer);
        $this->assertSame($owner->id, SocialAccount::query()->sole()->user_id);
    }

    public function test_apple_link_callback_requires_a_valid_link_intent_before_it_can_attach_an_account(): void
    {
        $customer = User::factory()->create();
        $this->actingAs($customer);
        $this->fakeSocialUser('apple', ['id' => 'apple-customer-1']);

        $this->get(route('social.apple-link.callback'))
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHasErrors('social');

        $this->assertAuthenticatedAs($customer);
        $this->assertDatabaseCount('social_accounts', 0);
    }

    public function test_apple_linking_requires_a_same_site_confirmation_from_the_original_customer(): void
    {
        $customer = User::factory()->create();
        $this->actingAs($customer)->withSession(['_token' => 'original-session-token']);
        $this->fakeSocialUser('apple', ['id' => 'apple-customer-1']);

        $redirect = $this->get(route('social.redirect', 'apple'));
        $intent = $redirect->getCookie('atha_social_apple_link_intent')?->getValue();

        $this->assertNotNull($intent);

        $callback = $this->withCookie('atha_social_apple_link_intent', $intent)
            ->get(route('social.apple-link.callback'))
            ->assertRedirect();

        $completionUrl = $callback->headers->get('Location');

        $this->assertNotNull($completionUrl);
        $this->withSession(['_token' => 'original-session-token'])->get($completionUrl)
            ->assertOk()
            ->assertSeeText('Hubungkan akun Apple?');

        $this->post(route('social.apple-link.confirm'))
            ->assertRedirect(route('profile.edit'));

        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $customer->id,
            'provider' => 'apple',
            'provider_user_id' => 'apple-customer-1',
        ]);
    }

    public function test_apple_link_completion_cannot_be_finished_by_a_different_customer(): void
    {
        $customer = User::factory()->create();
        $otherCustomer = User::factory()->create();
        $this->actingAs($customer);
        $this->fakeSocialUser('apple', ['id' => 'apple-customer-1']);

        $redirect = $this->get(route('social.redirect', 'apple'));
        $intent = $redirect->getCookie('atha_social_apple_link_intent')?->getValue();
        $callback = $this->withCookie('atha_social_apple_link_intent', $intent)
            ->get(route('social.apple-link.callback'));

        $this->actingAs($otherCustomer)
            ->get($callback->headers->get('Location'))
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHasErrors('social');

        $this->assertDatabaseCount('social_accounts', 0);
    }

    public function test_apple_link_completion_expires_when_the_original_session_is_invalidated(): void
    {
        $customer = User::factory()->create();
        $this->actingAs($customer)->withSession(['_token' => 'original-session-token']);
        $this->fakeSocialUser('apple', ['id' => 'apple-customer-1']);

        $redirect = $this->get(route('social.redirect', 'apple'));
        $intent = $redirect->getCookie('atha_social_apple_link_intent')?->getValue();
        $callback = $this->withCookie('atha_social_apple_link_intent', $intent)
            ->get(route('social.apple-link.callback'));

        $this->post(route('logout'));
        $this->actingAs($customer)
            ->withSession(['_token' => 'replacement-session-token'])
            ->get($callback->headers->get('Location'))
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHasErrors('social');

        $this->assertDatabaseCount('social_accounts', 0);
    }

    public function test_apple_accepts_its_post_callback_without_opening_csrf_exemptions_for_other_providers(): void
    {
        $this->fakeSocialUser('apple', [
            'id' => 'apple-customer-1',
            'email' => 'apple-customer@example.test',
        ]);

        $this->post(route('social.callback', 'apple'))
            ->assertRedirect(route('profile.edit'));

        $this->assertDatabaseHas('social_accounts', [
            'provider' => 'apple',
            'provider_user_id' => 'apple-customer-1',
        ]);
    }

    public function test_social_login_does_not_grant_a_socially_linked_admin_customer_access(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        SocialAccount::factory()->for($admin)->create([
            'provider' => 'google',
            'provider_user_id' => 'google-admin-1',
        ]);

        $this->fakeSocialUser('google', ['id' => 'google-admin-1']);

        $this->get(route('social.callback', 'google'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('social');

        $this->assertGuest();
    }

    public function test_social_user_can_skip_whatsapp_then_resume_a_valid_booking_draft_after_completing_profile(): void
    {
        $package = WeddingPackage::factory()->create(['is_active' => true]);
        $this->fakeSocialUser('google', [
            'id' => 'google-customer-1',
            'email' => 'social-customer@example.test',
        ]);

        $this->get(route('social.callback', 'google'));
        $this->withoutVite()->get(route('profile.edit'))
            ->assertOk()
            ->assertSeeText('Lengkapi Profil')
            ->assertSeeText('Lewati dulu');

        $this->post(route('social.profile.skip'))->assertRedirect(route('dashboard'));
        $this->get(route('packages.index'))->assertOk();

        $draft = [
            'wedding_package_id' => $package->id,
            'event_date' => today()->addMonth()->toDateString(),
            'couple_name' => 'Sinta dan Ikbal',
            'event_location' => 'Gedung Atha',
            'notes' => 'Dekorasi bernuansa rose.',
        ];

        $this->post(route('bookings.store'), $draft)
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('booking.resume', $draft)
            ->assertSessionHas('status', 'Nomor WhatsApp diperlukan untuk melanjutkan booking dan komunikasi terkait acara.');

        $this->put(route('profile.update'), [
            'name' => 'Social Customer',
            'email' => 'social-customer@example.test',
            'phone' => '628123456789',
        ])->assertRedirect(route('bookings.create', $package));

        $this->get(route('bookings.create', $package))
            ->assertOk()
            ->assertSee('Sinta dan Ikbal')
            ->assertSee('Gedung Atha');
    }

    public function test_confirming_an_existing_review_requires_whatsapp_again(): void
    {
        $customer = User::factory()->create(['phone' => '628123456789']);
        $package = WeddingPackage::factory()->create(['is_active' => true]);
        $this->actingAs($customer)->post(route('bookings.store'), [
            'wedding_package_id' => $package->id,
            'event_date' => today()->addMonth()->toDateString(),
            'couple_name' => 'Sinta dan Ikbal',
            'event_location' => 'Gedung Atha',
        ])->assertRedirect(route('bookings.review'));

        $customer->update(['phone' => null]);

        $this->post(route('bookings.confirm'), ['terms_accepted' => '1'])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('status', 'Nomor WhatsApp diperlukan untuk melanjutkan booking dan komunikasi terkait acara.');

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_social_user_can_set_a_password_without_current_password_then_use_email_login(): void
    {
        $this->fakeSocialUser('google', [
            'id' => 'google-customer-1',
            'email' => 'social-customer@example.test',
        ]);
        $this->get(route('social.callback', 'google'));
        $user = User::query()->where('email', 'social-customer@example.test')->sole();

        $this->put(route('profile.password'), [
            'password' => 'New-password123!',
            'password_confirmation' => 'New-password123!',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('New-password123!', $user->fresh()->password));
        $this->post(route('logout'));

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'New-password123!',
        ])->assertRedirect(route('dashboard'));
    }

    public function test_replacing_a_provider_verified_email_clears_its_verification_timestamp(): void
    {
        $this->fakeSocialUser('google', [
            'id' => 'google-customer-1',
            'email' => 'social-customer@example.test',
        ]);
        $this->get(route('social.callback', 'google'));
        $user = User::query()->where('email', 'social-customer@example.test')->sole();

        $this->assertNotNull($user->email_verified_at);

        $this->put(route('profile.update'), [
            'name' => $user->name,
            'email' => 'replaced@example.test',
            'phone' => null,
        ])->assertSessionHasNoErrors();

        $user = $user->fresh();

        $this->assertSame('replaced@example.test', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_social_identity_without_an_email_can_be_created_without_a_synthetic_email(): void
    {
        $this->fakeSocialUser('apple', [
            'id' => 'apple-customer-1',
            'name' => null,
            'nickname' => null,
            'email' => null,
        ]);

        $this->get(route('social.callback', 'apple'))->assertRedirect(route('profile.edit'));

        $user = User::query()->sole();

        $this->assertNull($user->email);
        $this->assertNull($user->password);
        $this->assertSame('Pelanggan Apple', $user->name);
    }

    public function test_unconfigured_provider_is_not_reachable_or_rendered(): void
    {
        config()->set('services.google.enabled', false);

        $this->get(route('social.redirect', 'google'))->assertNotFound();
        $this->get(route('social.callback', 'google'))->assertNotFound();
        $this->withoutVite()->get(route('login'))->assertDontSee('Lanjutkan dengan Google');
    }

    public function test_each_disabled_provider_is_not_reachable_or_rendered(): void
    {
        foreach (['google', 'apple', 'microsoft', 'facebook', 'x'] as $provider) {
            config()->set("services.{$provider}.enabled", false);

            $this->get(route('social.redirect', $provider))->assertNotFound();
            $this->get(route('social.callback', $provider))->assertNotFound();
            $this->withoutVite()->get(route('login'))->assertDontSee('data-social-provider="'.$provider.'"', false);

            config()->set("services.{$provider}.enabled", true);
        }
    }

    public function test_an_invalid_oauth_state_returns_a_safe_login_error_without_creating_an_account(): void
    {
        $driver = \Mockery::mock();
        $driver->shouldReceive('user')->once()->andThrow(new InvalidStateException);
        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($driver);

        $this->get(route('social.callback', 'google'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('social');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('social_accounts', 0);
    }

    private function fakeSocialUser(string $provider, array $attributes = []): void
    {
        if (in_array($provider, ['google', 'apple'], true) && ! array_key_exists('email_verified', $attributes) && ! array_key_exists('verified_email', $attributes)) {
            $attributes['email_verified'] = true;
        }

        Socialite::fake($provider, SocialiteUser::fake($attributes));
    }
}
