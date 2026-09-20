<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Filament\Resources\Bookings\Pages\ManageBookings;
use App\Filament\Resources\Galleries\Pages\ManageGalleries;
use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Filament\Resources\WeddingPackages\Pages\ManageWeddingPackages;
use App\Models\Booking;
use App\Models\Gallery;
use App\Models\User;
use App\Models\WeddingPackage;
use Filament\Actions\Testing\TestAction;
use Filament\Auth\Pages\Login as FilamentLogin;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Testing\File as TestingFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create(['is_admin' => true]));
    }

    public function test_admin_can_create_and_update_a_package(): void
    {
        Livewire::test(ManageWeddingPackages::class)
            ->callAction('create', data: $this->packageData())
            ->assertHasNoActionErrors();

        $package = WeddingPackage::query()->sole();

        Livewire::test(ManageWeddingPackages::class)
            ->callAction(TestAction::make('edit')->table($package), data: [
                'name' => 'Paket Gold Baru',
                'price' => 25000000,
                'is_active' => false,
            ])
            ->assertHasNoActionErrors();

        $this->assertSame('Paket Gold Baru', $package->refresh()->name);
        $this->assertSame(25000000, $package->price);
        $this->assertFalse($package->is_active);
    }

    #[DataProvider('invalidSlugs')]
    public function test_package_create_and_update_reject_invalid_slugs(string $slug): void
    {
        Livewire::test(ManageWeddingPackages::class)
            ->callAction('create', data: [...$this->packageData(), 'slug' => $slug])
            ->assertHasActionErrors(['slug' => 'regex']);

        $this->assertDatabaseCount('wedding_packages', 0);

        $package = WeddingPackage::factory()->create(['slug' => 'existing-package']);

        Livewire::test(ManageWeddingPackages::class)
            ->callAction(TestAction::make('edit')->table($package), data: ['slug' => $slug])
            ->assertHasActionErrors(['slug' => 'regex']);

        $this->assertSame('existing-package', $package->refresh()->slug);
    }

    public static function invalidSlugs(): array
    {
        return [
            'path separator' => ['gold/silver'],
            'query string' => ['gold?sale=1'],
            'space' => ['gold package'],
        ];
    }

    #[DataProvider('imageResources')]
    public function test_image_uploads_use_random_names_and_detected_extensions_and_survive_normal_edits(
        string $component,
        string $model,
        string $directory,
        array $data,
    ): void {
        $image = UploadedFile::fake()->image('image.png', 80, 80);
        $upload = (new TestingFile('client-name.jpg', $image->tempFile))->mimeType('image/png');

        Livewire::test($component)
            ->callAction('create', data: [...$data, 'image_path' => $upload])
            ->assertHasNoActionErrors();

        $record = $model::query()->sole();
        $originalPath = $record->image_path;
        $this->assertMatchesRegularExpression('#^'.$directory.'/[A-Za-z0-9]{40}\.png$#', $originalPath);
        Storage::disk('public')->assertExists($originalPath);

        Livewire::test($component)
            ->callAction(TestAction::make('edit')->table($record), data: ['description' => 'Keterangan diperbarui.'])
            ->assertHasNoActionErrors();

        $this->assertSame($originalPath, $record->refresh()->image_path);
        $this->assertSame('Keterangan diperbarui.', $record->description);
        Storage::disk('public')->assertExists($originalPath);
    }

    #[DataProvider('unsafeImageUploads')]
    public function test_image_uploads_reject_unsafe_files(
        string $component,
        string $model,
        string $directory,
        array $data,
        string $scenario,
    ): void {
        $image = UploadedFile::fake()->image('image.png');
        $upload = match ($scenario) {
            'html extension' => (new TestingFile('image.html', $image->tempFile))->mimeType('image/png'),
            'php extension' => (new TestingFile('image.php', $image->tempFile))->mimeType('image/png'),
            'non-image content' => UploadedFile::fake()->createWithContent('image.jpg', 'Not an image')->mimeType('text/plain'),
            'svg' => UploadedFile::fake()->createWithContent('image.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>'),
            'oversize' => UploadedFile::fake()->image('image.png')->size(2049),
            'dimensions' => UploadedFile::fake()->image('image.png', 4097, 10),
        };

        Livewire::test($component)
            ->callAction('create', data: [...$data, 'image_path' => $upload])
            ->assertHasActionErrors(['image_path']);

        $this->assertSame(0, $model::query()->count());
        $this->assertSame([], Storage::disk('public')->allFiles($directory));
    }

    #[DataProvider('imageResources')]
    public function test_image_uploads_reject_a_submitted_path_to_another_file(
        string $component,
        string $model,
        string $directory,
        array $data,
    ): void {
        $otherPath = $directory.'/other-image.jpg';
        Storage::disk('public')->put($otherPath, UploadedFile::fake()->image('image.jpg')->getContent());

        Livewire::test($component)
            ->callAction('create', data: [...$data, 'image_path' => [$otherPath]])
            ->assertHasActionErrors(['image_path']);

        $this->assertSame(0, $model::query()->count());
        Storage::disk('public')->assertExists($otherPath);
    }

    public static function imageResources(): array
    {
        return [
            'package' => [ManageWeddingPackages::class, WeddingPackage::class, 'packages', self::packageData()],
            'gallery' => [ManageGalleries::class, Gallery::class, 'gallery', [
                'title' => 'Dekorasi pernikahan',
                'description' => 'Dekorasi taman.',
                'sort_order' => 0,
                'is_published' => false,
            ]],
        ];
    }

    public static function unsafeImageUploads(): iterable
    {
        foreach (self::imageResources() as $resource => $data) {
            foreach (['html extension', 'php extension', 'non-image content', 'svg', 'oversize', 'dimensions'] as $scenario) {
                yield $resource.' '.$scenario => [...$data, $scenario];
            }
        }
    }

    public function test_admin_booking_details_preserve_the_original_package_terms(): void
    {
        $booking = Booking::factory()->create([
            'package_name_snapshot' => 'Paket Gold Lama',
            'package_price_snapshot' => 20000000,
            'package_description_snapshot' => 'Dekorasi dan dokumentasi awal.',
            'notes' => 'Acara di rumah pelanggan.',
        ]);
        $booking->weddingPackage->update([
            'name' => 'Paket Platinum Baru',
            'price' => 35000000,
            'description' => 'Layanan baru.',
            'is_active' => false,
        ]);

        Livewire::test(ManageBookings::class)
            ->mountAction(TestAction::make('view')->table($booking))
            ->assertActionDataSet([
                'package_name_snapshot' => 'Paket Gold Lama',
                'package_price_snapshot' => 'Rp 20.000.000',
                'package_description_snapshot' => 'Dekorasi dan dokumentasi awal.',
                'notes' => 'Acara di rumah pelanggan.',
                'terms_snapshot' => $booking->terms_snapshot,
            ]);
    }

    public function test_admin_can_change_booking_status_and_cancellation_requires_a_reason(): void
    {
        $booking = Booking::factory()->create();

        Livewire::test(ManageBookings::class)
            ->callAction(TestAction::make('changeStatus')->table($booking), data: ['status' => 'accepted'])
            ->assertHasNoActionErrors();

        $this->assertSame(BookingStatus::Accepted, $booking->refresh()->status);

        Livewire::test(ManageBookings::class)
            ->callAction(TestAction::make('changeStatus')->table($booking), data: ['status' => 'cancelled'])
            ->assertHasActionErrors(['cancellation_reason' => 'required_if']);

        $this->assertSame(BookingStatus::Accepted, $booking->refresh()->status);

        Livewire::test(ManageBookings::class)
            ->callAction(TestAction::make('changeStatus')->table($booking), data: [
                'status' => 'cancelled',
                'cancellation_reason' => 'Pelanggan membatalkan acara melalui WhatsApp.',
            ])
            ->assertHasNoActionErrors();

        $this->assertSame(BookingStatus::Cancelled, $booking->refresh()->status);
        $this->assertSame('Pelanggan membatalkan acara melalui WhatsApp.', $booking->cancellation_reason);
    }

    public function test_booking_status_options_exclude_the_current_status_and_reject_a_forged_no_op(): void
    {
        $booking = Booking::factory()->create();

        Livewire::test(ManageBookings::class)
            ->mountAction(TestAction::make('changeStatus')->table($booking))
            ->assertFormFieldExists('status', fn (Select $field): bool => array_keys($field->getOptions()) === ['accepted', 'cancelled'])
            ->fillForm(['status' => 'pending'])
            ->callMountedAction()
            ->assertHasActionErrors(['status']);

        $this->assertSame(BookingStatus::Pending, $booking->refresh()->status);
    }

    public function test_terminal_bookings_cannot_mount_the_status_action(): void
    {
        foreach ([BookingStatus::Completed, BookingStatus::Cancelled] as $status) {
            $booking = Booking::factory()->create(['status' => $status]);

            Livewire::test(ManageBookings::class)
                ->assertActionHidden(TestAction::make('changeStatus')->table($booking))
                ->call('mountAction', 'changeStatus', [], ['table' => true, 'recordKey' => $booking->id])
                ->assertActionNotMounted();

            $this->assertSame($status, $booking->refresh()->status);
        }
    }

    public function test_customer_cannot_access_any_admin_resource(): void
    {
        $this->actingAs(User::factory()->create());

        foreach ([ManageWeddingPackages::class, ManageGalleries::class, ManageBookings::class, ManageUsers::class] as $component) {
            Livewire::test($component)->assertForbidden();
        }
    }

    public function test_customer_credentials_are_rejected_by_the_filament_login(): void
    {
        auth()->logout();

        $customer = User::factory()->create([
            'password' => Hash::make('Password123!'),
        ]);

        Livewire::test(FilamentLogin::class)
            ->fillForm([
                'email' => $customer->email,
                'password' => 'Password123!',
            ])
            ->call('authenticate')
            ->assertHasFormErrors(['email']);

        $this->assertGuest();
    }

    public function test_admin_created_customer_email_is_normalized_before_validation_and_can_login(): void
    {
        Livewire::test(ManageUsers::class)
            ->callAction('create', data: [
                'name' => 'Sinta Rahma',
                'email' => ' Sinta@Example.test ',
                'phone' => '628123456789',
                'password' => 'Password123!',
            ])
            ->assertHasNoActionErrors();

        $customer = User::query()->where('email', 'sinta@example.test')->firstOrFail();
        $this->assertFalse($customer->is_admin);

        Livewire::test(ManageUsers::class)
            ->callAction('create', data: [
                'name' => 'Duplicate Sinta',
                'email' => 'SINTA@EXAMPLE.TEST',
                'password' => 'Password123!',
            ])
            ->assertHasActionErrors(['email' => 'unique']);

        $this->post(route('logout'));
        $this->post(route('login.store'), ['email' => 'sinta@example.test', 'password' => 'Password123!'])
            ->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($customer);
    }

    public function test_admin_can_edit_customer_without_replacing_password(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);

        Livewire::test(ManageUsers::class)
            ->callAction(TestAction::make('edit')->table($customer), data: [
                'name' => 'Nama Diperbarui',
                'email' => $customer->email,
                'phone' => '628123456789',
            ])
            ->assertHasNoActionErrors();

        $this->assertSame('Nama Diperbarui', $customer->refresh()->name);
    }

    public function test_admin_cannot_remove_email_from_a_customer_who_uses_password_login(): void
    {
        $passwordCustomer = User::factory()->create(['is_admin' => false]);
        $socialCustomer = User::factory()->create([
            'email' => null,
            'is_admin' => false,
            'password' => null,
        ]);

        Livewire::test(ManageUsers::class)
            ->callAction(TestAction::make('edit')->table($passwordCustomer), data: [
                'name' => $passwordCustomer->name,
                'email' => '',
                'phone' => $passwordCustomer->phone,
            ])
            ->assertHasActionErrors(['email' => 'required']);

        $this->assertNotNull($passwordCustomer->refresh()->email);

        Livewire::test(ManageUsers::class)
            ->callAction(TestAction::make('edit')->table($socialCustomer), data: [
                'name' => 'Pelanggan Sosial Diperbarui',
                'email' => null,
                'phone' => null,
            ])
            ->assertHasNoActionErrors();

        $this->assertNull($socialCustomer->refresh()->email);
        $this->assertSame('Pelanggan Sosial Diperbarui', $socialCustomer->name);
    }

    private static function packageData(): array
    {
        return [
            'name' => 'Paket Gold',
            'slug' => 'paket-gold',
            'description' => 'Dekorasi dan dokumentasi.',
            'price' => 20000000,
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
