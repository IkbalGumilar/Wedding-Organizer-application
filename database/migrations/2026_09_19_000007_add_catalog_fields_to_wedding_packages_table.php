<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wedding_packages', function (Blueprint $table): void {
            $table->string('tagline', 255)->nullable()->after('name');
            $table->json('sections')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('wedding_packages', function (Blueprint $table): void {
            $table->dropColumn(['tagline', 'sections']);
        });
    }
};
