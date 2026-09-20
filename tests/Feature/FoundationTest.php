<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_are_available_without_authentication(): void
    {
        $this->withoutVite();

        foreach (['home', 'about', 'packages.index', 'gallery.index', 'contact'] as $routeName) {
            $this->get(route($routeName))->assertOk();
        }
    }

    public function test_https_forwarded_requests_generate_https_urls(): void
    {
        $response = $this->withHeaders([
            'Host' => 'public.example.test',
            'X-Forwarded-Host' => 'public.example.test',
            'X-Forwarded-Proto' => 'https',
        ])->withoutVite()->get('/');

        $response->assertSee('href="https://public.example.test/tentang-kami"', false);
    }

    public function test_authentication_pages_are_available_and_dashboard_requires_login(): void
    {
        $this->withoutVite()
            ->get(route('login'))
            ->assertOk()
            ->assertSee('name="email"', false);

        $this->get(route('register'))->assertOk()->assertSee('name="password_confirmation"', false);
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get('/passkeys/login/options')->assertNotFound();
    }

    public function test_public_and_customer_layouts_include_an_accessible_persistent_theme_toggle(): void
    {
        $this->withoutVite()
            ->get(route('home'))
            ->assertOk()
            ->assertSee('data-theme-toggle', false)
            ->assertSee('aria-label="Aktifkan tema gelap"', false)
            ->assertSee("localStorage.getItem('atha-theme')", false)
            ->assertSee('prefers-color-scheme', false)
            ->assertSee('dark:bg-[#191615]', false);

        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-theme-toggle', false);
    }

    public function test_registration_ignores_admin_privileges_and_customer_can_logout(): void
    {
        $this->withoutVite();

        $this->post(route('register.store'), [
            'name' => 'Sinta Rahma',
            'email' => 'sinta@example.test',
            'phone' => '628123456789',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'is_admin' => true,
        ])->assertRedirect('/dashboard');

        $user = User::query()->where('email', 'sinta@example.test')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertFalse($user->is_admin);
        $this->assertTrue(Hash::check('Password123!', $user->password));

        $this->post(route('logout'))->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_registration_normalizes_email_before_unique_validation(): void
    {
        User::factory()->create(['email' => 'sinta@example.test']);

        $this->withoutVite()
            ->post(route('register.store'), [
                'name' => 'Duplicate Sinta',
                'email' => ' SINTA@EXAMPLE.TEST ',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ])
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('users', 1);
    }

    public function test_customer_can_login_with_valid_credentials_and_invalid_credentials_are_rejected(): void
    {
        $user = User::factory()->create([
            'email' => 'sinta@example.test',
            'password' => Hash::make('Password123!'),
        ]);

        $this->withoutVite()
            ->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'Password123!',
            ])
            ->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
        $this->post(route('logout'));

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_five_attempts(): void
    {
        $user = User::factory()->create(['email' => 'limit@example.test']);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'wrong-password',
            ])->assertSessionHasErrors('email');
        }

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertTooManyRequests();
    }

    public function test_customer_cannot_access_admin_panel(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        foreach (['/admin', '/admin/bookings', '/admin/galleries', '/admin/users', '/admin/wedding-packages'] as $uri) {
            $this->get($uri)->assertForbidden();
        }

        $this->get('/admin/register')->assertNotFound();
    }

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_authenticated_customer_cannot_use_the_admin_login_page(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->get('/admin/login')
            ->assertRedirect('/admin');
    }

    public function test_admin_can_access_filament_dashboard(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSeeText('Atha Decoration');
    }

    public function test_admin_can_access_filament_resources(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin);

        foreach (['/admin/bookings', '/admin/galleries', '/admin/users', '/admin/wedding-packages'] as $uri) {
            $this->get($uri)->assertOk();
        }
    }
}
