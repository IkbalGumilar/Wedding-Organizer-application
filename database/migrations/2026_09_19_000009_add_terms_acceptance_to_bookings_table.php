<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->text('terms_snapshot')->nullable()->after('package_sections_snapshot');
            $table->timestamp('terms_accepted_at')->nullable()->after('terms_snapshot');
            $table->string('terms_version', 64)->nullable()->after('terms_accepted_at');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropColumn(['terms_snapshot', 'terms_accepted_at', 'terms_version']);
        });
    }
};
