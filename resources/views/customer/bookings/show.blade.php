@extends('layouts.app')

@section('title', 'Detail Booking — Atha Decoration')

@section('content')
    <main id="main-content" class="mx-auto max-w-4xl px-5 py-12 lg:px-8 lg:py-20">
        <a href="{{ route('bookings.index') }}" class="text-sm font-semibold text-rose-700 hover:text-rose-900 dark:text-rose-300 dark:hover:text-rose-200">← Kembali ke booking</a>

        <div class="mt-8 flex flex-col justify-between gap-5 sm:flex-row sm:items-start">
            <div class="min-w-0">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-rose-700 dark:text-rose-300">Detail booking #{{ $booking->id }}</p>
                <h1 class="mt-4 break-words font-serif text-5xl text-stone-900 dark:text-stone-50">{{ $booking->couple_name ?: $booking->user->name }}</h1>
                <p class="mt-3 text-stone-600 dark:text-stone-300">{{ $booking->package_name_snapshot }}</p>
            </div>
            <x-booking-status :status="$booking->status" />
        </div>

        <div class="mt-10 grid gap-5 sm:grid-cols-2">
            <div class="rounded-3xl border border-stone-200 bg-white p-7 dark:border-[#625752] dark:bg-[#302824]">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-stone-500 dark:text-stone-400">Tanggal acara</p>
                <p class="mt-3 font-serif text-3xl text-stone-900 dark:text-stone-50">{{ $booking->event_date->translatedFormat('d F Y') }}</p>
            </div>
            <div class="rounded-3xl border border-stone-200 bg-white p-7 dark:border-[#625752] dark:bg-[#302824]">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-stone-500 dark:text-stone-400">Lokasi acara</p>
                <p class="mt-3 whitespace-pre-line break-words leading-7 text-stone-800 dark:text-stone-100">{{ $booking->event_location ?: 'Belum diisi' }}</p>
            </div>
        </div>

        <div class="mt-5 grid gap-5 sm:grid-cols-2">
            <div class="rounded-3xl border border-stone-200 bg-white p-7 dark:border-[#625752] dark:bg-[#302824]">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-stone-500 dark:text-stone-400">Nilai paket saat diajukan</p>
                <p class="mt-3 font-serif text-3xl text-stone-900 dark:text-stone-50">{{ $booking->formatted_price }}</p>
            </div>
            <div class="rounded-3xl border border-stone-200 bg-white p-7 dark:border-[#625752] dark:bg-[#302824]">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-stone-500 dark:text-stone-400">Status pembayaran</p>
                <p class="mt-3 font-serif text-3xl text-stone-900 dark:text-stone-50">{{ $booking->payment_status?->label() ?? 'Belum Bayar' }}</p>
            </div>
        </div>

        <article class="mt-5 rounded-3xl border border-stone-200 bg-white p-7 dark:border-[#625752] dark:bg-[#302824] sm:p-10">
            <h2 class="font-serif text-3xl text-stone-900 dark:text-stone-50">Detail paket</h2>
            <p class="mt-5 whitespace-pre-line break-words leading-8 text-stone-600 dark:text-stone-300">{{ $booking->package_description_snapshot }}</p>

            <div class="mt-10">
                <x-package-sections :sections="$booking->package_sections_snapshot" />
            </div>

            <div class="mt-10">
                <h2 class="font-serif text-3xl text-stone-900 dark:text-stone-50">Vendor dan personel acara</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    @foreach ([
                        'makeup' => 'Make Up',
                        'henna' => 'Henna',
                        'photographer' => 'Photographer',
                        'mc' => 'MC',
                        'entertainment' => 'Hiburan',
                        'traditional_ceremony' => 'Upacara Adat',
                        'eo' => 'EO',
                        'videographer' => 'Videographer',
                        'wedding_content_creator' => 'Wedding Content Creator',
                    ] as $field => $label)
                        <div class="rounded-2xl bg-stone-50 p-4 dark:bg-[#3b302c]">
                            <p class="text-xs font-semibold uppercase tracking-[0.12em] text-stone-500 dark:text-stone-400">{{ $label }}</p>
                            <p class="mt-2 break-words text-sm text-stone-800 dark:text-stone-100">{{ filled($booking->{$field}) ? $booking->{$field} : $label.' belum ditentukan' }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            @if ($booking->notes)
                <h2 class="mt-10 font-serif text-3xl text-stone-900 dark:text-stone-50">Catatan Anda</h2>
                <p class="mt-5 whitespace-pre-line break-words leading-8 text-stone-600 dark:text-stone-300">{{ $booking->notes }}</p>
            @endif

            @if ($booking->status === \App\Enums\BookingStatus::Cancelled && $booking->cancellation_reason)
                <div class="mt-8 rounded-2xl bg-rose-50 p-5 text-sm text-rose-800 dark:bg-rose-950/40 dark:text-rose-200">
                    <p class="font-semibold">Catatan pembatalan</p>
                    <p class="mt-2 break-words">{{ $booking->cancellation_reason }}</p>
                </div>
            @endif
        </article>

        @if (filled($booking->terms_snapshot))
            <section class="mt-5 rounded-3xl border border-rose-200 bg-rose-50 p-7 dark:border-rose-900/60 dark:bg-rose-950/30 sm:p-10">
                <h2 class="font-serif text-3xl text-stone-900 dark:text-stone-50">Ketentuan yang Disetujui</h2>
                <p class="mt-4 whitespace-pre-line break-words text-sm leading-7 text-stone-700 dark:text-stone-200">{{ $booking->terms_snapshot }}</p>
                <p class="mt-6 text-xs text-stone-500 dark:text-stone-400">
                    Disetujui pada {{ $booking->terms_accepted_at?->translatedFormat('d F Y, H:i') ?? '—' }}
                    @if ($booking->terms_version)
                        · Versi {{ substr($booking->terms_version, 0, 12) }}
                    @endif
                </p>
            </section>
        @endif
    </main>
@endsection
