@extends('layouts.app')

@section('title', 'Booking Saya — Atha Decoration')

@section('content')
    <main id="main-content" class="mx-auto max-w-6xl px-5 py-12 lg:px-8 lg:py-20">
        <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-rose-700 dark:text-rose-300">Area Anda</p>
                <h1 class="mt-3 font-serif text-4xl text-stone-900 dark:text-stone-50">Booking saya</h1>
                <p class="mt-3 text-stone-600 dark:text-stone-300">Pantau permintaan dan kabar terbaru dari tim kami.</p>
            </div>
            <a href="{{ route('packages.index') }}" class="rounded-full bg-stone-900 px-5 py-3 text-center text-sm font-semibold text-white hover:bg-rose-800 dark:bg-rose-600 dark:hover:bg-rose-500">Ajukan booking baru</a>
        </div>

        <div class="mt-10 overflow-hidden rounded-3xl border border-stone-200 bg-white dark:border-[#625752] dark:bg-[#302824]">
            @forelse ($bookings as $booking)
                <a href="{{ route('bookings.show', $booking) }}" class="flex flex-col gap-3 border-b border-stone-100 p-6 transition last:border-0 hover:bg-rose-50/40 dark:border-stone-700 dark:hover:bg-rose-950/30 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-stone-500 dark:text-stone-400">{{ $booking->event_date->translatedFormat('d F Y') }} · #{{ $booking->id }}</p>
                        <h2 class="mt-2 break-words font-serif text-2xl text-stone-900 dark:text-stone-50">{{ $booking->package_name_snapshot }}</h2>
                    </div>
                    <x-booking-status :status="$booking->status" />
                </a>
            @empty
                <div class="p-12 text-center">
                    <p class="font-serif text-2xl text-stone-800 dark:text-stone-100">Belum ada booking.</p>
                    <p class="mt-3 text-sm text-stone-600 dark:text-stone-300">Pilih paket yang sesuai untuk memulai percakapan.</p>
                    <a href="{{ route('packages.index') }}" class="mt-6 inline-block font-semibold text-rose-700 dark:text-rose-300">Lihat paket →</a>
                </div>
            @endforelse
        </div>

        <div class="mt-8">{{ $bookings->links() }}</div>
    </main>
@endsection
