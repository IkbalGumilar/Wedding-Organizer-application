<?php

namespace Tests\Feature;

use App\Actions\Bookings\ChangeBookingStatus;
use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Filament\Resources\Bookings\Pages\ManageBookings;
use App\Filament\Resources\WeddingPackages\Pages\ManageWeddingPackages;
use App\Models\Booking;
use App\Models\User;
use App\Models\WeddingPackage;
use App\Services\BookingAvailabilityService;
use Carbon\CarbonImmutable;
use Database\Seeders\WeddingPackageCatalogSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use Tests\TestCase;

class EventDetailAndCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_seeder_imports_nine_packages_idempotently_with_safe_prices(): void
    {
        Artisan::call('db:seed', ['--class' => WeddingPackageCatalogSeeder::class]);
        Artisan::call('db:seed', ['--class' => WeddingPackageCatalogSeeder::class]);

        $this->assertDatabaseCount('wedding_packages', 9);
        $this->assertSame(9, WeddingPackage::query()->distinct('slug')->count('slug'));
        $this->assertSame(17047000, WeddingPackage::query()->where('slug', 'peluk-kasih')->value('price'));
        $this->assertSame(55000000, WeddingPackage::query()->where('slug', 'emerald-wedding-by-atha')->value('price'));
        $this->assertNotEmpty(WeddingPackage::query()->where('slug', 'selamanya')->firstOrFail()->sections);
    }

    public function test_public_catalog_lists_packages_and_hides_empty_sections(): void
    {
        $package = WeddingPackage::factory()->create([
            'is_active' => true,
            'tagline' => 'Paket pilihan keluarga',
            'sections' => [
                ['title' => 'Dekorasi', 'items' => ['Pelaminan 6 m']],
                ['title' => 'Section Kosong', 'items' => []],
            ],
        ]);

        $this->withoutVite()
            ->get(route('packages.index'))
            ->assertOk()
            ->assertSeeText($package->name)
            ->assertSeeText($package->tagline);

        $this->withoutVite()
            ->get(route('packages.show', $package))
            ->assertOk()
            ->assertSeeText('Dekorasi')
            ->assertSeeText('Pelaminan 6 m')
            ->assertDontSeeText('Section Kosong');
    }

    public function test_customer_booking_persists_event_detail_and_package_sections_snapshot(): void
    {
        $user = User::factory()->create(['phone' => '628123456789']);
        $package = WeddingPackage::factory()->create([
            'is_active' => true,
            'sections' => [['title' => 'Dokumentasi', 'items' => ['Photographer 1 orang']]],
        ]);

        $package->update(['sections' => [
            ['title' => 'Dokumentasi', 'items' => ['Photographer 1 orang']],
            ['title' => 'Term of Payment', 'items' => ['Booking 30%', 'Pelunasan maksimal H-7']],
        ]]);

        $this->actingAs($user)->post(route('bookings.store'), [
            'wedding_package_id' => $package->id,
            'package_price' => $package->price,
            'event_date' => today()->addMonth()->toDateString(),
            'couple_name' => 'Dina dan Raka',
            'event_location' => "Gedung Atha\nJalan Mawar No. 1",
            'notes' => 'Membutuhkan Wedding Content Creator.',
            'makeup' => 'Forged vendor must be ignored',
            'payment_status' => PaymentStatus::Paid->value,
        ])->assertRedirect(route('bookings.review'));

        $this->post(route('bookings.confirm'))->assertSessionHasErrors('terms_accepted');
        $this->post(route('bookings.confirm'), ['terms_accepted' => '1'])->assertRedirect();

        $booking = Booking::query()->sole();

        $this->assertSame('Dina dan Raka', $booking->couple_name);
        $this->assertSame("Gedung Atha\nJalan Mawar No. 1", $booking->event_location);
        $this->assertSame(PaymentStatus::Unpaid, $booking->payment_status);
        $this->assertNull($booking->makeup);
        $this->assertSame($package->sections, $booking->package_sections_snapshot);
    }

    public function test_customer_sees_event_detail_and_vendor_empty_states(): void
    {
        $user = User::factory()->create();
        $booking = Booking::factory()->create([
            'user_id' => $user->id,
            'couple_name' => 'Dina dan Raka',
            'event_location' => 'Gedung Atha',
            'makeup' => 'Gea Kamelia',
            'package_sections_snapshot' => [['title' => 'Bonus', 'items' => ['Buku tamu 2 pcs']]],
        ]);

        $this->actingAs($user)
            ->withoutVite()
            ->get(route('bookings.show', $booking))
            ->assertOk()
            ->assertSeeText('Dina dan Raka')
            ->assertSeeText('Gedung Atha')
            ->assertSeeText('Gea Kamelia')
            ->assertSeeText('Photographer belum ditentukan')
            ->assertSeeText('Buku tamu 2 pcs')
            ->assertSeeText('Ketentuan yang Disetujui')
            ->assertSeeText('Booking 30%');
    }

    public function test_admin_can_edit_event_detail_without_editing_customer_owned_fields(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $booking = Booking::factory()->create([
            'user_id' => User::factory()->create(['phone' => '08123456789'])->id,
            'couple_name' => 'Dina dan Raka',
            'event_location' => 'Gedung Atha',
        ]);

        Livewire::test(ManageBookings::class)
            ->assertTableActionHasUrl('contactCustomer', 'https://wa.me/628123456789', $booking)
            ->assertTableActionShouldOpenUrlInNewTab('contactCustomer', $booking);

        Livewire::test(ManageBookings::class)
            ->callAction(TestAction::make('edit')->table($booking), data: [
                'makeup' => 'Gea Kamelia',
                'henna' => 'Nana Henna Art',
                'photographer' => 'Ajwa Photo',
                'mc' => 'Sri Astuti',
                'entertainment' => 'ABS Production',
                'traditional_ceremony' => 'Getar Pasundan',
                'eo' => 'Atha Decoration',
                'videographer' => 'EU Film',
                'wedding_content_creator' => 'Atha Content Team',
                'payment_status' => PaymentStatus::Deposit->value,
            ])
            ->assertHasNoActionErrors();

        $booking->refresh();
        $this->assertSame('Gea Kamelia', $booking->makeup);
        $this->assertSame(PaymentStatus::Deposit, $booking->payment_status);
        $this->assertSame('Dina dan Raka', $booking->couple_name);
        $this->assertSame('Gedung Atha', $booking->event_location);
    }

    public function test_admin_can_manage_flexible_package_sections(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(ManageWeddingPackages::class)
            ->callAction('create', data: [
                'name' => 'Paket Section Test',
                'slug' => 'paket-section-test',
                'description' => 'Paket yang memiliki section fleksibel.',
                'price' => 17000000,
                'sort_order' => 10,
                'is_active' => true,
                'sections' => [
                    ['title' => 'Dekorasi', 'items' => [['item' => 'Pelaminan 6 m'], ['item' => 'Tenda 6 x 12 m']]],
                    ['title' => 'Bonus', 'items' => [['item' => 'Buku tamu 1 pcs']]],
                ],
            ])
            ->assertHasNoActionErrors();

        $package = WeddingPackage::query()->where('slug', 'paket-section-test')->sole();
        $this->assertSame('Dekorasi', $package->sections[0]['title']);
        $this->assertSame('Buku tamu 1 pcs', $package->sections[1]['items'][0]);
    }

    public function test_capacity_allows_three_bookings_rejects_fourth_and_releases_cancelled_slot(): void
    {
        $package = WeddingPackage::factory()->create(['is_active' => true]);
        $date = today()->addMonths(2)->toDateString();
        $users = User::factory()->count(4)->create(['phone' => '628123456789']);

        foreach ($users->take(3) as $user) {
            $this->confirmBooking($user, $package, $date);
        }

        $this->assertSame(3, Booking::query()->whereDate('event_date', $date)->count());

        $this->actingAs($users[3])->post(route('bookings.store'), [
            'wedding_package_id' => $package->id,
            'event_date' => $date,
            'couple_name' => 'Slot Keempat',
            'event_location' => 'Gedung Atha',
        ])->assertRedirect(route('bookings.review'));

        $this->post(route('bookings.confirm'), ['terms_accepted' => '1'])
            ->assertSessionHasErrors(['event_date' => 'Tanggal ini sudah penuh. Silakan pilih tanggal lain.']);

        $cancelled = Booking::query()->whereDate('event_date', $date)->firstOrFail();
        app(ChangeBookingStatus::class)->handle($cancelled, BookingStatus::Cancelled, 'Pelanggan membatalkan acara.');

        $this->post(route('bookings.confirm'), ['terms_accepted' => '1'])->assertRedirect();
        $this->assertSame(4, Booking::query()->whereDate('event_date', $date)->count());
        $this->assertSame(BookingStatus::Cancelled, $cancelled->refresh()->status);
    }

    public function test_availability_counts_only_non_cancelled_bookings_and_marks_full_dates(): void
    {
        $package = WeddingPackage::factory()->create(['is_active' => true]);
        $date = today()->addMonth();
        Booking::factory()->count(2)->create(['wedding_package_id' => $package->id, 'event_date' => $date, 'status' => BookingStatus::Pending]);
        Booking::factory()->create(['wedding_package_id' => $package->id, 'event_date' => $date, 'status' => BookingStatus::Cancelled]);

        $status = app(BookingAvailabilityService::class)->forDate(CarbonImmutable::parse($date->toDateString()));
        $this->assertSame(2, $status['used']);
        $this->assertSame(1, $status['remaining']);
        $this->assertSame('last_slot', $status['status']);

        Booking::factory()->create(['wedding_package_id' => $package->id, 'event_date' => $date, 'status' => BookingStatus::Accepted]);
        $this->assertSame('full', app(BookingAvailabilityService::class)->forDate(CarbonImmutable::parse($date->toDateString()))['status']);
        $this->assertSame('past', app(BookingAvailabilityService::class)->forDate(CarbonImmutable::parse(today()->subDay()->toDateString()))['status']);

        $this->actingAs(User::factory()->create())
            ->get(route('bookings.availability', ['month' => $date->format('Y-m')]))
            ->assertOk()
            ->assertJsonPath('month', $date->format('Y-m'))
            ->assertJsonFragment(['date' => $date->toDateString(), 'status' => 'full']);
    }

    public function test_package_changes_do_not_modify_terms_snapshot_or_accepted_timestamp(): void
    {
        $user = User::factory()->create(['phone' => '628123456789']);
        $package = WeddingPackage::factory()->create(['is_active' => true]);
        $date = today()->addMonth()->toDateString();

        $this->confirmBooking($user, $package, $date);
        $booking = Booking::query()->sole();
        $terms = $booking->terms_snapshot;
        $acceptedAt = $booking->terms_accepted_at;

        $package->update(['sections' => [['title' => 'Term of Payment', 'items' => ['Term baru']]]]);

        $this->assertSame($terms, $booking->refresh()->terms_snapshot);
        $this->assertTrue($acceptedAt->equalTo($booking->terms_accepted_at));
    }

    private function confirmBooking(User $user, WeddingPackage $package, string $date): void
    {
        $this->actingAs($user)->post(route('bookings.store'), [
            'wedding_package_id' => $package->id,
            'event_date' => $date,
            'couple_name' => $user->name,
            'event_location' => 'Gedung Atha',
        ])->assertRedirect(route('bookings.review'));

        $this->post(route('bookings.confirm'), ['terms_accepted' => '1'])->assertRedirect();
    }
}
