<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('wedding_package_id')->constrained()->restrictOnDelete();
            $table->date('event_date')->index();
            $table->string('status', 20)->default('pending')->index();
            $table->text('notes')->nullable();
            $table->string('cancellation_reason', 500)->nullable();
            $table->string('package_name_snapshot', 150);
            $table->text('package_description_snapshot');
            $table->unsignedBigInteger('package_price_snapshot');
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'event_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
