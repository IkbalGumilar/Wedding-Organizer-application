@extends('layouts.app')

@section('content')
<main id="main-content">
    <section class="relative overflow-hidden">
        <div class="mx-auto grid max-w-7xl items-center gap-12 px-5 py-20 lg:grid-cols-[1.05fr_.95fr] lg:px-8 lg:py-28">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-rose-700 dark:text-rose-300">Wedding organizer &amp; decoration</p>
                <h1 class="mt-6 max-w-2xl font-serif text-5xl leading-[1.02] tracking-tight text-stone-900 dark:text-stone-50 sm:text-7xl">Hari istimewa, dirancang dengan <span class="italic text-rose-700 dark:text-rose-300">rasa.</span></h1>
                <p class="mt-7 max-w-xl text-lg leading-8 text-stone-600 dark:text-stone-300">Kami membantu Anda menghadirkan perayaan yang hangat, rapi, dan terasa personal — dari konsep hingga hari H.</p>
                <div class="mt-9 flex flex-wrap gap-3">
                    <a href="{{ route('packages.index') }}" class="rounded-full bg-stone-900 px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-rose-800 dark:bg-rose-600 dark:hover:bg-rose-500">Lihat paket wedding</a>
                    <a href="{{ route('contact') }}" class="rounded-full border border-stone-300 bg-white px-6 py-3.5 text-sm font-semibold text-stone-700 transition hover:border-rose-700 hover:text-rose-700 dark:border-[#625752] dark:bg-[#302824] dark:text-stone-100 dark:hover:border-rose-400 dark:hover:text-rose-200">Konsultasi gratis</a>
                </div>
            </div>
            <div class="relative min-h-[360px] overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-rose-100 via-[#f5dfd5] to-amber-100 shadow-2xl shadow-rose-900/10 dark:from-rose-950 dark:via-stone-900 dark:to-amber-950">
                <div class="absolute -right-12 -top-12 h-48 w-48 rounded-full border-[22px] border-white/40 dark:border-stone-700/60"></div>
                <div class="absolute bottom-8 left-8 right-8 rounded-3xl border border-white/60 bg-white/60 p-7 backdrop-blur-sm dark:border-stone-700/80 dark:bg-stone-900/70">
                    <p class="font-serif text-3xl text-stone-900 dark:text-stone-50">“Detail kecil membuat kenangan terasa besar.”</p>
                    <p class="mt-3 text-sm text-stone-600 dark:text-stone-300">Atha Decoration</p>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-white py-20 dark:bg-[#1f1a18]">
        <div class="mx-auto max-w-7xl px-5 lg:px-8">
            <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
                <div><p class="text-sm font-semibold uppercase tracking-[0.2em] text-rose-700 dark:text-rose-300">Pilihan untuk Anda</p><h2 class="mt-3 font-serif text-4xl text-stone-900 dark:text-stone-50">Paket yang bisa disesuaikan</h2></div>
                <a href="{{ route('packages.index') }}" class="text-sm font-semibold text-rose-700 hover:text-rose-900 dark:text-rose-300 dark:hover:text-rose-200">Lihat semua paket →</a>
            </div>
            <div class="mt-10 grid gap-6 md:grid-cols-3">
                @forelse ($packages as $package)
                    <a href="{{ route('packages.show', $package) }}" class="group rounded-3xl border border-stone-200 bg-[#fbf8f4] p-7 transition hover:-translate-y-1 hover:border-rose-200 hover:shadow-xl hover:shadow-rose-900/5 dark:border-[#625752] dark:bg-[#302824] dark:hover:border-rose-600 dark:hover:shadow-black/30">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-stone-500 dark:text-stone-400">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</p>
                        <h3 class="mt-6 font-serif text-3xl text-stone-900 dark:text-stone-50">{{ $package->name }}</h3>
                        <p class="mt-4 line-clamp-3 text-sm leading-6 text-stone-600 dark:text-stone-300">{{ $package->description }}</p>
                        <p class="mt-6 font-semibold text-rose-700 dark:text-rose-300">{{ $package->formatted_price }}</p>
                    </a>
                @empty
                    <div class="rounded-3xl border border-dashed border-stone-300 p-8 text-stone-600 dark:border-stone-600 dark:text-stone-300 md:col-span-3">Paket wedding sedang kami siapkan. Hubungi kami untuk pilihan terbaru.</div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-5 py-20 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-[.8fr_1.2fr] lg:items-center">
            <div><p class="text-sm font-semibold uppercase tracking-[0.2em] text-rose-700 dark:text-rose-300">Mengapa Atha</p><h2 class="mt-3 font-serif text-4xl leading-tight text-stone-900 dark:text-stone-50">Merayakan cinta dengan cara Anda.</h2><p class="mt-6 leading-7 text-stone-600 dark:text-stone-300">Setiap pasangan punya cerita sendiri. Kami mendengarkan, merapikan detail, dan menerjemahkannya menjadi perayaan yang nyaman untuk dikenang.</p><a href="{{ route('about') }}" class="mt-7 inline-block text-sm font-semibold text-rose-700 hover:text-rose-900 dark:text-rose-300 dark:hover:text-rose-200">Kenali kami lebih dekat →</a></div>
            <div class="grid grid-cols-2 gap-4">
                @forelse ($galleries as $gallery)
                    <div class="aspect-[4/3] overflow-hidden rounded-3xl bg-rose-100 dark:bg-rose-950/50 {{ $loop->iteration === 1 ? 'col-span-2' : '' }}">
                        <img src="{{ Storage::disk('public')->url($gallery->image_path) }}" alt="{{ $gallery->title }}" class="h-full w-full object-cover" loading="lazy">
                    </div>
                @empty
                    <div class="col-span-2 grid aspect-[2/1] place-items-center rounded-3xl bg-gradient-to-br from-rose-100 to-amber-100 text-center dark:from-rose-950 dark:to-amber-950"><p class="font-serif text-2xl text-stone-700 dark:text-stone-200">Cerita indah segera hadir di sini.</p></div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="mx-5 overflow-hidden rounded-[2rem] bg-stone-900 px-6 py-16 text-center text-white sm:px-12 lg:mx-auto lg:max-w-7xl">
        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-rose-300">Mari mulai bercerita</p><h2 class="mx-auto mt-4 max-w-2xl font-serif text-4xl sm:text-5xl">Siap merencanakan hari yang tak terlupakan?</h2>
        @if (filled(config('services.whatsapp.number')))
            <a href="https://wa.me/{{ config('services.whatsapp.number') }}" target="_blank" rel="noopener noreferrer" class="mt-8 inline-block rounded-full bg-rose-500 px-7 py-3.5 text-sm font-semibold text-white transition hover:bg-rose-400">Hubungi kami di WhatsApp</a>
        @else
            <a href="{{ route('contact') }}" class="mt-8 inline-block rounded-full bg-rose-500 px-7 py-3.5 text-sm font-semibold text-white transition hover:bg-rose-400">Hubungi kami</a>
        @endif
    </section>
</main>
@endsection
