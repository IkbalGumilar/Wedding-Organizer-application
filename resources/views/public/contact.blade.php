@extends('layouts.app')

@section('title', 'Kontak — Atha Decoration')

@section('content')
    <main id="main-content" class="mx-auto max-w-7xl px-5 py-16 lg:px-8 lg:py-24">
        <div class="max-w-2xl">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-rose-700 dark:text-rose-300">Kontak</p>
            <h1 class="mt-5 font-serif text-5xl leading-tight text-stone-900 dark:text-stone-50">Mari ngobrol tentang hari istimewa Anda.</h1>
            <p class="mt-6 text-lg leading-8 text-stone-600 dark:text-stone-300">Ceritakan rencana, tanggal, dan suasana yang Anda bayangkan untuk perayaan Anda.</p>
        </div>

        <div class="mt-12 max-w-2xl">
            @if (filled(config('services.whatsapp.number')))
                <a href="https://wa.me/{{ config('services.whatsapp.number') }}" target="_blank" rel="noopener noreferrer" class="block rounded-3xl bg-stone-900 p-7 text-white transition hover:bg-rose-800 dark:bg-rose-700 dark:hover:bg-rose-600 sm:p-10">
                    <p class="text-sm font-semibold uppercase tracking-[0.16em] text-rose-300">WhatsApp</p>
                    <p class="mt-4 font-serif text-3xl">Mulai percakapan →</p>
                    <p class="mt-3 text-sm text-stone-200">+{{ config('services.whatsapp.number') }}</p>
                </a>
            @else
                <div class="rounded-3xl border border-stone-200 bg-white p-7 dark:border-[#625752] dark:bg-[#302824] sm:p-10">
                    <h2 class="font-serif text-3xl text-stone-900 dark:text-stone-50">Informasi kontak segera tersedia.</h2>
                    <p class="mt-4 leading-7 text-stone-600 dark:text-stone-300">Sementara itu, Anda dapat melihat pilihan paket dan mengajukan permintaan booking melalui website.</p>
                    <a href="{{ route('packages.index') }}" class="mt-6 inline-block font-semibold text-rose-700 hover:text-rose-900 dark:text-rose-300 dark:hover:text-rose-200">Lihat paket wedding →</a>
                </div>
            @endif
        </div>
    </main>
@endsection
