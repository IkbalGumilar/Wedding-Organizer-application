<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_updates_only_the_current_user_and_normalizes_email(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($owner)->put(route('profile.update'), [
            'name' => 'Sinta', 'email' => 'SINTA@EXAMPLE.TEST', 'phone' => '628123456789',
            'id' => $other->id, 'is_admin' => true,
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSame('sinta@example.test', $owner->refresh()->email);
        $this->assertFalse($owner->is_admin);
        $this->assertSame($other->email, $other->fresh()->email);

        $this->put(route('profile.update'), [
            'name' => 'Sinta', 'email' => strtoupper($other->email), 'phone' => '628123456789',
        ])->assertSessionHasErrors('email');
    }

    public function test_password_change_requires_current_password_and_revokes_remember_token(): void
    {
        $user = User::factory()->create(['remember_token' => 'old-token']);
        $this->actingAs($user)->put(route('profile.password'), [
            'current_password' => 'incorrect',
            'password' => 'New-password123!', 'password_confirmation' => 'New-password123!',
        ])->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('password', $user->fresh()->password));

        $this->put(route('profile.password'), [
            'current_password' => 'password',
            'password' => 'New-password123!', 'password_confirmation' => 'New-password123!',
        ])->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('New-password123!', $user->fresh()->password));
        $this->assertNotSame('old-token', $user->fresh()->remember_token);
    }

    public function test_password_reset_link_and_token_flow_work_without_sending_real_mail(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->withoutVite()->get(route('password.request'))->assertOk();
        $this->post(route('password.email'), ['email' => $user->email])->assertSessionHasNoErrors();
        $notification = Notification::sent($user, ResetPassword::class)->sole();

        $this->get(route('password.reset', ['token' => $notification->token, 'email' => $user->email]))->assertOk();
        $data = ['email' => $user->email, 'password' => 'New-password123!', 'password_confirmation' => 'New-password123!'];
        $this->post(route('password.update'), [...$data, 'token' => 'invalid-token'])->assertSessionHasErrors('email');
        $this->post(route('password.update'), [...$data, 'token' => $notification->token])->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('New-password123!', $user->fresh()->password));
        $this->assertDatabaseCount('password_reset_tokens', 0);
    }

    public function test_authenticated_account_pages_render(): void
    {
        $this->actingAs(User::factory()->create())->withoutVite();
        $this->get(route('profile.edit'))->assertOk();
        $this->get(route('password.confirm'))->assertOk();
    }

    public function test_customer_password_fields_render_accessible_visibility_toggles(): void
    {
        $this->withoutVite();

        $pages = [
            [route('login'), 1],
            [route('register'), 2],
            [route('password.request'), 0],
            [route('password.reset', ['token' => 'preview-token', 'email' => 'customer@example.test']), 2],
        ];

        foreach ($pages as [$url, $expectedToggleCount]) {
            $content = $this->get($url)->assertOk()->getContent();

            $this->assertSame($expectedToggleCount, substr_count($content, 'data-password-toggle-target'));

            if ($expectedToggleCount > 0) {
                $this->assertStringContainsString('type="button"', $content);
                $this->assertStringContainsString('type="password"', $content);
                $this->assertStringContainsString('aria-label="Tampilkan kata sandi"', $content);
                $this->assertStringContainsString('aria-pressed="false"', $content);
                $this->assertStringContainsString('data-password-show-icon', $content);
                $this->assertStringContainsString('data-password-hide-icon', $content);
            }
        }

        $content = $this->actingAs(User::factory()->create())
            ->get(route('profile.edit'))
            ->assertOk()
            ->getContent();

        $this->assertSame(3, substr_count($content, 'data-password-toggle-target'));
    }
}
