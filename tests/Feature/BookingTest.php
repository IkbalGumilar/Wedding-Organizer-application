<?php

namespace Tests\Feature;

use App\Actions\Bookings\ChangeBookingStatus;
use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\User;
use App\Models\WeddingPackage;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_submit_a_booking_with_a_package_snapshot(): void
    {
        $user = User::factory()->create(['phone' => '628123456789']);
        $package = WeddingPackage::query()->create([
            'name' => 'Paket Intimate',
            'slug' => 'paket-intimate',
            'description' => 'Dekorasi dan koordinasi untuk acara intimate.',
            'sections' => [['title' => 'Term of Payment', 'items' => ['Booking 30%', 'Pelunasan maksimal H-7']]],
            'price' => 25000000,
            'is_active' => true,
        ]);

        $this->actingAs($user)->post(route('bookings.store'), [
            'wedding_package_id' => $package->id,
            'package_price' => $package->price,
            'event_date' => today()->addMonths(2)->toDateString(),
            'couple_name' => 'Ikbal dan Sinta',
            'event_location' => 'Gedung Atha, Jalan Mawar No. 1',
            'notes' => 'Acara keluarga kecil.',
            'user_id' => User::factory()->create()->id,
            'status' => 'accepted',
            'package_price_snapshot' => 1,
        ])->assertRedirect(route('bookings.review'));

        $this->assertDatabaseCount('bookings', 0);
        $this->withoutVite()->get(route('bookings.review'))->assertOk()->assertSeeText('Booking 30%');
        $this->post(route('bookings.confirm'), ['terms_accepted' => '1'])->assertRedirect();

        $booking = Booking::query()->firstOrFail();
        $this->assertSame(BookingStatus::Pending, $booking->status);
        $this->assertSame($user->id, $booking->user_id);
        $this->assertSame($package->name, $booking->package_name_snapshot);
        $this->assertSame($package->price, $booking->package_price_snapshot);
        $this->assertSame('Ikbal dan Sinta', $booking->couple_name);
        $this->assertSame('Gedung Atha, Jalan Mawar No. 1', $booking->event_location);
        $this->assertSame(PaymentStatus::Unpaid, $booking->payment_status);
        $this->assertNotNull($booking->terms_accepted_at);
        $this->assertSame($package->termsSnapshot(), $booking->terms_snapshot);

        $package->update(['name' => 'Paket Intimate Baru', 'price' => 30000000]);
        $this->assertSame('Paket Intimate', $booking->refresh()->package_name_snapshot);
        $this->assertSame(25000000, $booking->package_price_snapshot);
    }

    public function test_guest_is_redirected_to_login_with_the_selected_booking_form_as_intended_url(): void
    {
        $package = WeddingPackage::factory()->create(['is_active' => true]);
        $bookingFormUrl = route('bookings.create', $package);

        $this->get($bookingFormUrl)
            ->assertRedirect(route('login'));

        $this->assertSame($bookingFormUrl, session('url.intended'));

        $user = User::factory()->create();
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect($bookingFormUrl);
        $this->withoutVite()->get($bookingFormUrl)->assertOk()->assertSeeText($package->formatted_price);
    }

    public function test_customer_must_have_a_phone_number_before_booking(): void
    {
        $user = User::factory()->create(['phone' => null]);
        $package = WeddingPackage::factory()->create(['is_active' => true]);

        $this->actingAs($user)
            ->post(route('bookings.store'), [
                'wedding_package_id' => $package->id,
                'event_date' => today()->addMonth()->toDateString(),
                'couple_name' => 'Pelanggan Demo',
                'event_location' => 'Rumah pelanggan',
            ])
            ->assertRedirect(route('profile.edit'));

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_customer_cannot_view_another_customers_booking(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $package = WeddingPackage::factory()->create();
        $booking = Booking::factory()->create([
            'user_id' => $owner->id,
            'wedding_package_id' => $package->id,
        ]);

        $this->actingAs($other)->get(route('bookings.show', $booking))->assertNotFound();
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'user_id' => $owner->id]);

        $this->withoutVite()->get(route('bookings.index'))->assertOk()->assertDontSeeText($booking->package_name_snapshot);
        $this->actingAs($owner)->get(route('bookings.show', $booking))->assertOk()->assertSeeText($booking->package_name_snapshot);
        $this->put(route('bookings.show', $booking), ['status' => 'accepted'])->assertStatus(405);
        $this->assertSame(BookingStatus::Pending, $booking->refresh()->status);
    }

    public function test_inactive_package_cannot_be_booked_even_when_id_is_submitted(): void
    {
        $user = User::factory()->create(['phone' => '628123456789']);
        $package = WeddingPackage::factory()->create(['is_active' => false]);

        $this->actingAs($user)
            ->post(route('bookings.store'), [
                'wedding_package_id' => $package->id,
                'event_date' => today()->addMonth()->toDateString(),
                'couple_name' => 'Pelanggan Demo',
                'event_location' => 'Rumah pelanggan',
            ])
            ->assertSessionHasErrors('wedding_package_id');

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_client_supplied_price_cannot_override_server_snapshot(): void
    {
        $user = User::factory()->create(['phone' => '628123456789']);
        $package = WeddingPackage::factory()->create(['is_active' => true, 'price' => 25000000]);
        $package->update(['price' => 30000000]);

        $this->actingAs($user)
            ->post(route('bookings.store'), [
                'wedding_package_id' => $package->id,
                'package_price' => 25000000,
                'event_date' => today()->addMonth()->toDateString(),
                'couple_name' => 'Pelanggan Demo',
                'event_location' => 'Rumah pelanggan',
            ])
            ->assertRedirect(route('bookings.review'));

        $this->post(route('bookings.confirm'), ['terms_accepted' => '1'])->assertRedirect();

        $this->assertDatabaseHas('bookings', ['package_price_snapshot' => 30000000]);
    }

    public function test_booking_status_transitions_are_validated_server_side(): void
    {
        $booking = Booking::factory()->create(['status' => BookingStatus::Pending]);

        app(ChangeBookingStatus::class)->handle($booking, BookingStatus::Accepted);
        $this->assertSame(BookingStatus::Accepted, $booking->refresh()->status);

        $this->expectException(DomainException::class);
        app(ChangeBookingStatus::class)->handle($booking, BookingStatus::Pending);
    }

    public function test_invalid_date_and_malformed_package_id_are_rejected(): void
    {
        $package = WeddingPackage::factory()->create(['is_active' => true]);
        $this->actingAs(User::factory()->create(['phone' => '628123456789']))
            ->post(route('bookings.store'), [
                'wedding_package_id' => $package->id.'abc',
                'event_date' => now()->subDay()->toDateString(),
                'couple_name' => 'Pelanggan Demo',
                'event_location' => 'Rumah pelanggan',
            ])->assertSessionHasErrors(['wedding_package_id', 'event_date']);
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_completed_and_cancelled_bookings_are_terminal_even_with_a_stale_model(): void
    {
        $booking = Booking::factory()->create(['event_date' => today(), 'status' => BookingStatus::Accepted]);
        $staleBooking = $booking->fresh();
        app(ChangeBookingStatus::class)->handle($booking, BookingStatus::Completed);
        $this->assertSame(BookingStatus::Completed, $booking->refresh()->status);

        $this->expectException(DomainException::class);
        app(ChangeBookingStatus::class)->handle($staleBooking, BookingStatus::Cancelled, 'Stale request');
    }

    public function test_completion_before_event_and_cancellation_without_reason_are_rejected(): void
    {
        $booking = Booking::factory()->create(['status' => BookingStatus::Accepted]);

        try {
            app(ChangeBookingStatus::class)->handle($booking, BookingStatus::Completed);
            $this->fail('Expected future completion to be rejected.');
        } catch (DomainException $exception) {
            $this->assertSame('Booking hanya dapat ditandai selesai setelah tanggal acara.', $exception->getMessage());
        }

        try {
            app(ChangeBookingStatus::class)->handle($booking, BookingStatus::Cancelled);
            $this->fail('Expected cancellation without a reason to be rejected.');
        } catch (DomainException $exception) {
            $this->assertSame('Alasan pembatalan wajib diisi.', $exception->getMessage());
        }
    }
}
