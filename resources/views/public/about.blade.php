@extends('layouts.app')

@section('title', 'Tentang Kami — Atha Decoration')

@section('content')
    <main id="main-content" class="mx-auto max-w-7xl px-5 py-16 lg:px-8 lg:py-24">
        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-rose-700 dark:text-rose-300">Tentang kami</p>

        <div class="mt-5 grid gap-12 lg:grid-cols-[.8fr_1.2fr]">
            <h1 class="font-serif text-5xl leading-tight text-stone-900 dark:text-stone-50 sm:text-6xl">Ruang untuk perayaan yang berarti.</h1>
            <div class="space-y-5 text-lg leading-8 text-stone-600 dark:text-stone-300">
                <p>Atha Decoration hadir untuk membantu Anda merencanakan perayaan pernikahan. Mulailah dengan melihat pilihan paket, mengenali detail layanan, dan menceritakan kebutuhan acara Anda.</p>
                <p>Setiap pengajuan booking ditinjau terlebih dahulu. Tanggal dan detail acara akan dikonfirmasi oleh tim sebelum booking diterima.</p>
                <div class="rounded-3xl border border-stone-200 bg-white p-7 dark:border-[#625752] dark:bg-[#302824]">
                    <h2 class="font-serif text-3xl text-stone-900 dark:text-stone-50">Mulai dari cerita Anda.</h2>
                    <p class="mt-3 text-base leading-7">Pilih paket yang sesuai, tentukan tanggal, lalu sertakan catatan mengenai perayaan yang Anda bayangkan.</p>
                    <a href="{{ route('packages.index') }}" class="mt-5 inline-block text-sm font-semibold text-rose-700 hover:text-rose-900 dark:text-rose-300 dark:hover:text-rose-200">Kenali pilihan paket →</a>
                </div>
            </div>
        </div>
    </main>
@endsection
