@extends('layouts.app')

@section('title', $weddingPackage->name.' — Atha Decoration')

@section('content')
    <main id="main-content" class="mx-auto max-w-5xl px-5 py-16 lg:px-8 lg:py-24">
        <a href="{{ route('packages.index') }}" class="text-sm font-semibold text-rose-700 hover:text-rose-900 dark:text-rose-300 dark:hover:text-rose-200">← Kembali ke paket</a>

        @if ($weddingPackage->image_path)
            <img
                src="{{ Storage::disk('public')->url($weddingPackage->image_path) }}"
                alt="{{ $weddingPackage->name }}"
                class="mt-8 aspect-[16/9] w-full rounded-3xl object-cover"
                fetchpriority="high"
            >
        @endif

        <div class="mt-10 grid gap-12 lg:grid-cols-[1.1fr_.9fr]">
            <div class="min-w-0">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-rose-700 dark:text-rose-300">Paket wedding</p>
                <h1 class="mt-5 break-words font-serif text-5xl leading-tight text-stone-900 dark:text-stone-50">{{ $weddingPackage->name }}</h1>
                @if ($weddingPackage->tagline)
                    <p class="mt-4 text-sm font-semibold uppercase tracking-[0.14em] text-rose-700 dark:text-rose-300">{{ $weddingPackage->tagline }}</p>
                @endif
                <p class="mt-8 whitespace-pre-line break-words leading-8 text-stone-600 dark:text-stone-300">{{ $weddingPackage->description }}</p>
            </div>

            <aside class="h-fit rounded-3xl border border-stone-200 bg-white p-7 shadow-xl shadow-stone-900/5 dark:border-[#625752] dark:bg-[#302824] dark:shadow-black/30">
                <p class="text-sm font-semibold uppercase tracking-[0.16em] text-stone-500 dark:text-stone-400">Harga paket</p>
                <p class="mt-4 font-serif text-4xl text-stone-900 dark:text-stone-50">{{ $weddingPackage->formatted_price }}</p>
                <p class="mt-4 text-sm leading-6 text-stone-600 dark:text-stone-300">Ajukan tanggal pilihan Anda. Tim kami akan meninjau ketersediaan sebelum mengonfirmasi booking.</p>
                <a href="{{ route('bookings.create', $weddingPackage) }}" class="mt-7 block rounded-full bg-stone-900 px-5 py-3 text-center text-sm font-semibold text-white transition hover:bg-rose-800 dark:bg-rose-600 dark:hover:bg-rose-500">Ajukan booking</a>
                @guest
                    <p class="mt-3 text-center text-xs text-stone-500 dark:text-stone-400">Masuk atau daftar untuk mengajukan booking.</p>
                @endguest
            </aside>
        </div>

        <div class="mt-14 rounded-3xl border border-stone-200 bg-white p-7 dark:border-[#625752] dark:bg-[#302824] sm:p-10">
            <x-package-sections :sections="$weddingPackage->sections" />
        </div>
    </main>
@endsection
