@extends('layouts.app')

@section('title', 'Paket Wedding — Atha Decoration')

@section('content')
    <main id="main-content" class="mx-auto max-w-7xl px-5 py-16 lg:px-8 lg:py-24">
        <div class="max-w-2xl">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-rose-700 dark:text-rose-300">Paket wedding</p>
            <h1 class="mt-5 font-serif text-5xl leading-tight text-stone-900 dark:text-stone-50">Pilih ruang untuk cerita Anda.</h1>
            <p class="mt-6 text-lg leading-8 text-stone-600 dark:text-stone-300">Kenali pilihan paket dan detail layanannya untuk merencanakan perayaan Anda.</p>
        </div>

        <div class="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @forelse ($packages as $package)
                <x-package-card :package="$package" />
            @empty
                <div class="rounded-3xl border border-dashed border-stone-300 p-10 text-stone-600 dark:border-stone-600 dark:text-stone-300 md:col-span-2 lg:col-span-3">
                    Paket wedding sedang kami siapkan. Silakan kunjungi halaman kontak untuk informasi lebih lanjut.
                    <a href="{{ route('contact') }}" class="mt-4 block font-semibold text-rose-700 dark:text-rose-300">Informasi kontak →</a>
                </div>
            @endforelse
        </div>

        <div class="mt-10">{{ $packages->links() }}</div>
    </main>
@endsection
