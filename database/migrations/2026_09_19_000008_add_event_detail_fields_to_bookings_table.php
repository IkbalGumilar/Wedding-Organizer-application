<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->string('couple_name', 150)->nullable()->after('event_date');
            $table->text('event_location')->nullable()->after('couple_name');
            $table->json('package_sections_snapshot')->nullable()->after('package_description_snapshot');
            $table->string('payment_status', 20)->default('unpaid')->index()->after('status');
            $table->string('makeup')->nullable()->after('payment_status');
            $table->string('henna')->nullable()->after('makeup');
            $table->string('photographer')->nullable()->after('henna');
            $table->string('mc')->nullable()->after('photographer');
            $table->string('entertainment')->nullable()->after('mc');
            $table->string('traditional_ceremony')->nullable()->after('entertainment');
            $table->string('eo')->nullable()->after('traditional_ceremony');
            $table->string('videographer')->nullable()->after('eo');
            $table->string('wedding_content_creator')->nullable()->after('videographer');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropColumn([
                'couple_name',
                'event_location',
                'package_sections_snapshot',
                'payment_status',
                'makeup',
                'henna',
                'photographer',
                'mc',
                'entertainment',
                'traditional_ceremony',
                'eo',
                'videographer',
                'wedding_content_creator',
            ]);
        });
    }
};
