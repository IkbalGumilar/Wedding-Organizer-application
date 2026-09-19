@extends('layouts.app')

@section('title', 'Review Booking — Atha Decoration')

@section('content')
    <main id="main-content" class="mx-auto max-w-4xl px-5 py-12 lg:px-8 lg:py-20">
        <div class="max-w-2xl">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-rose-700 dark:text-rose-300">Langkah terakhir</p>
            <h1 class="mt-4 font-serif text-5xl text-stone-900 dark:text-stone-50">Review booking Anda</h1>
            <p class="mt-4 leading-7 text-stone-600 dark:text-stone-300">Periksa kembali data acara dan ketentuan paket sebelum mengirim permintaan booking.</p>
        </div>

        <div class="mt-10 grid gap-6 lg:grid-cols-[1.1fr_.9fr]">
            <section class="rounded-3xl border border-stone-200 bg-white p-7 shadow-xl shadow-stone-900/5 dark:border-[#625752] dark:bg-[#302824] dark:shadow-black/20 sm:p-10">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-stone-500 dark:text-stone-400">Informasi acara</p>
                <dl class="mt-6 space-y-5">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-[0.12em] text-stone-500 dark:text-stone-400">Nama pasangan</dt>
                        <dd class="mt-1 break-words text-lg text-stone-900 dark:text-stone-100">{{ $review['couple_name'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-[0.12em] text-stone-500 dark:text-stone-400">Paket</dt>
                        <dd class="mt-1 break-words text-lg text-stone-900 dark:text-stone-100">{{ $package->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-[0.12em] text-stone-500 dark:text-stone-400">Harga saat review</dt>
                        <dd class="mt-1 font-serif text-3xl text-stone-900 dark:text-stone-100">{{ $package->formatted_price }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-[0.12em] text-stone-500 dark:text-stone-400">Tanggal acara</dt>
                        <dd class="mt-1 text-lg text-stone-900 dark:text-stone-100">{{ \Carbon\CarbonImmutable::parse($review['event_date'])->translatedFormat('d F Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-[0.12em] text-stone-500 dark:text-stone-400">Lokasi</dt>
                        <dd class="mt-1 whitespace-pre-line break-words text-lg text-stone-900 dark:text-stone-100">{{ $review['event_location'] }}</dd>
                    </div>
                    @if (filled($review['notes'] ?? null))
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-[0.12em] text-stone-500 dark:text-stone-400">Catatan acara</dt>
                            <dd class="mt-1 whitespace-pre-line break-words text-stone-700 dark:text-stone-200">{{ $review['notes'] }}</dd>
                        </div>
                    @endif
                </dl>
            </section>

            <aside class="h-fit rounded-3xl border border-rose-200 bg-rose-50 p-7 dark:border-rose-900/60 dark:bg-rose-950/30 sm:p-8">
                <h2 class="font-serif text-3xl text-stone-900 dark:text-stone-50">Ketentuan booking</h2>
                <p class="mt-3 text-sm leading-6 text-stone-600 dark:text-stone-300">Ketentuan di bawah ini akan disimpan sebagai snapshot pada booking Anda.</p>
                <p class="mt-6 whitespace-pre-line break-words text-sm leading-7 text-stone-700 dark:text-stone-200">{{ $review['terms_snapshot'] }}</p>

                @if ($errors->any())
                    <div class="mt-6 rounded-2xl border border-rose-300 bg-white/70 px-4 py-3 text-sm text-rose-800 dark:border-rose-800 dark:bg-rose-950/40 dark:text-rose-200" role="alert">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('bookings.confirm') }}" class="mt-7">
                    @csrf
                    <label class="flex cursor-pointer gap-3 text-sm leading-6 text-stone-700 dark:text-stone-200">
                        <input type="checkbox" name="terms_accepted" value="1" required class="mt-1 h-4 w-4 rounded border-stone-300 text-rose-700 focus:ring-rose-600 dark:border-[#625752] dark:bg-[#3b302c]">
                        <span>Saya telah membaca, memahami, dan menyetujui syarat dan ketentuan booking ini.</span>
                    </label>
                    <button type="submit" class="mt-6 w-full rounded-full bg-stone-900 px-6 py-3 text-sm font-semibold text-white transition hover:bg-rose-800 focus:outline-none focus:ring-2 focus:ring-rose-600 focus:ring-offset-2 dark:bg-rose-600 dark:hover:bg-rose-500 dark:focus:ring-rose-400 dark:focus:ring-offset-stone-900">Setujui &amp; buat booking</button>
                </form>

                <a href="{{ route('bookings.create', $package) }}" class="mt-4 block text-center text-sm font-semibold text-rose-700 hover:text-rose-900 dark:text-rose-300 dark:hover:text-rose-200">← Kembali dan ubah data</a>
            </aside>
        </div>

        <section class="mt-8 rounded-3xl border border-stone-200 bg-white p-7 dark:border-[#625752] dark:bg-[#302824] sm:p-10">
            <h2 class="font-serif text-3xl text-stone-900 dark:text-stone-50">Ringkasan isi paket</h2>
            <div class="mt-7">
                <x-package-sections :sections="$package->sections" />
            </div>
        </section>
    </main>
@endsection
