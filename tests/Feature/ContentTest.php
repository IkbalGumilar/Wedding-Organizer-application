<?php

namespace Tests\Feature;

use App\Models\Gallery;
use App\Models\WeddingPackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_catalog_only_exposes_active_packages_and_published_gallery_items(): void
    {
        $activePackage = WeddingPackage::factory()->create([
            'name' => 'Paket Aktif',
            'slug' => 'paket-aktif',
            'is_active' => true,
        ]);
        WeddingPackage::factory()->create([
            'name' => 'Paket Draft',
            'slug' => 'paket-draft',
            'is_active' => false,
        ]);
        Gallery::query()->create([
            'title' => 'Foto Tayang',
            'image_path' => 'gallery/published.jpg',
            'is_published' => true,
        ]);
        Gallery::query()->create([
            'title' => 'Foto Draft',
            'image_path' => 'gallery/draft.jpg',
            'is_published' => false,
        ]);

        $this->withoutVite()->get(route('packages.index'))
            ->assertOk()
            ->assertSeeText('Paket Aktif')
            ->assertDontSeeText('Paket Draft');

        $this->withoutVite()->get(route('packages.show', $activePackage))->assertOk();
        $this->withoutVite()->get('/paket-wedding/paket-draft')->assertNotFound();

        $this->withoutVite()->get(route('gallery.index'))
            ->assertOk()
            ->assertSeeText('Foto Tayang')
            ->assertDontSeeText('Foto Draft');
    }

    public function test_replacing_or_deleting_a_content_image_removes_the_old_public_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('packages/old.jpg', 'old');
        Storage::disk('public')->put('packages/new.jpg', 'new');

        $package = WeddingPackage::factory()->create(['image_path' => 'packages/old.jpg']);
        $package->update(['image_path' => 'packages/new.jpg']);
        Storage::disk('public')->assertMissing('packages/old.jpg');
        Storage::disk('public')->assertExists('packages/new.jpg');

        $package->delete();
        Storage::disk('public')->assertMissing('packages/new.jpg');
    }
}
