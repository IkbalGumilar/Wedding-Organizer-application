@extends('layouts.app')

@section('title', 'Ajukan Booking — Atha Decoration')

@section('content')
    <main id="main-content" class="mx-auto max-w-3xl px-5 py-12 lg:px-8 lg:py-20">
        <a href="{{ route('packages.show', $weddingPackage) }}" class="text-sm font-semibold text-rose-700 hover:text-rose-900 dark:text-rose-300 dark:hover:text-rose-200">← Kembali ke paket</a>

        <div class="mt-8">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-rose-700 dark:text-rose-300">Ajukan booking</p>
            <h1 class="mt-4 break-words font-serif text-5xl text-stone-900 dark:text-stone-50">{{ $weddingPackage->name }}</h1>
            <p class="mt-4 leading-7 text-stone-600 dark:text-stone-300">Anda akan memeriksa kembali data dan menyetujui ketentuan sebelum booking dibuat.</p>
        </div>

        <form method="POST" action="{{ route('bookings.store') }}" class="mt-10 rounded-3xl border border-stone-200 bg-white p-7 shadow-xl shadow-stone-900/5 dark:border-[#625752] dark:bg-[#302824] dark:shadow-black/20 sm:p-10">
            @csrf
            <input type="hidden" name="wedding_package_id" value="{{ $weddingPackage->id }}">

            <div class="mb-8 rounded-2xl bg-rose-50 p-5 dark:bg-rose-950/30">
                <p class="text-sm font-semibold text-stone-600 dark:text-stone-300">Harga paket saat pengajuan</p>
                <p class="mt-2 font-serif text-3xl text-stone-900 dark:text-stone-50">{{ $weddingPackage->formatted_price }}</p>
                <p class="mt-3 text-sm leading-6 text-stone-600 dark:text-stone-300">Harga akan diambil kembali dari database saat review dan konfirmasi.</p>
            </div>

            <div>
                <label for="couple_name" class="text-sm font-semibold text-stone-700 dark:text-stone-200">Nama pelanggan / pasangan</label>
                <input id="couple_name" name="couple_name" type="text" maxlength="150" value="{{ old('couple_name', auth()->user()->name) }}" required class="mt-2 w-full rounded-xl border border-stone-300 bg-stone-50 px-4 py-3 text-stone-900 focus:border-rose-600 focus:ring-rose-600 dark:border-[#625752] dark:bg-[#3b302c] dark:text-stone-100">
            </div>

            <div class="mt-6" data-availability-calendar data-endpoint="{{ route('bookings.availability') }}" data-initial-date="{{ old('event_date') }}" data-min-date="{{ now()->toDateString() }}">
                <div class="flex items-center justify-between gap-4">
                    <label for="event_date" class="text-sm font-semibold text-stone-700 dark:text-stone-200">Tanggal acara</label>
                    <div class="flex items-center gap-2">
                        <button type="button" data-calendar-prev class="rounded-full border border-stone-300 px-3 py-1 text-sm text-stone-700 hover:border-rose-600 hover:text-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-600 dark:border-stone-600 dark:text-stone-200" aria-label="Bulan sebelumnya">←</button>
                        <span data-calendar-month class="min-w-28 text-center text-sm font-semibold text-stone-700 dark:text-stone-200"></span>
                        <button type="button" data-calendar-next class="rounded-full border border-stone-300 px-3 py-1 text-sm text-stone-700 hover:border-rose-600 hover:text-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-600 dark:border-stone-600 dark:text-stone-200" aria-label="Bulan berikutnya">→</button>
                    </div>
                </div>
                <input id="event_date" name="event_date" type="date" min="{{ now()->toDateString() }}" value="{{ old('event_date') }}" required aria-describedby="event-date-help" class="mt-3 w-full rounded-xl border border-stone-300 bg-stone-50 px-4 py-3 text-stone-900 focus:border-rose-600 focus:ring-rose-600 dark:border-[#625752] dark:bg-[#3b302c] dark:text-stone-100">
                <div class="mt-4 grid grid-cols-7 gap-1 text-center text-[0.65rem] font-semibold uppercase tracking-wide text-stone-500 dark:text-stone-400" aria-hidden="true">
                    <span>Min</span><span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span>
                </div>
                <div data-calendar-grid class="mt-2 grid grid-cols-7 gap-1" role="grid" aria-label="Ketersediaan tanggal acara"></div>
                <div class="mt-3 flex flex-wrap gap-x-3 gap-y-1 text-xs font-medium text-stone-500 dark:text-stone-400" aria-label="Keterangan ketersediaan tanggal">
                    <span>Tersedia</span><span aria-hidden="true">•</span><span>1 slot tersisa</span><span aria-hidden="true">•</span><span>Penuh</span>
                </div>
                <p id="event-date-help" data-calendar-message aria-live="polite" class="mt-2 text-sm text-stone-500 dark:text-stone-400">Pilih tanggal untuk melihat ketersediaan.</p>
            </div>

            <div class="mt-6">
                <label for="notes" class="text-sm font-semibold text-stone-700 dark:text-stone-200">Catatan tambahan <span class="font-normal text-stone-500 dark:text-stone-400">(opsional)</span></label>
                <textarea id="notes" name="notes" rows="6" maxlength="2000" class="mt-2 w-full rounded-xl border border-stone-300 bg-stone-50 px-4 py-3 text-stone-900 focus:border-rose-600 focus:ring-rose-600 dark:border-[#625752] dark:bg-[#3b302c] dark:text-stone-100" placeholder="Ceritakan kebutuhan tambahan, jumlah tamu, atau suasana yang Anda bayangkan.">{{ old('notes') }}</textarea>
            </div>

            <div class="mt-6">
                <label for="event_location" class="text-sm font-semibold text-stone-700 dark:text-stone-200">Lokasi acara</label>
                <textarea id="event_location" name="event_location" rows="3" maxlength="2000" required class="mt-2 w-full rounded-xl border border-stone-300 bg-stone-50 px-4 py-3 text-stone-900 focus:border-rose-600 focus:ring-rose-600 dark:border-[#625752] dark:bg-[#3b302c] dark:text-stone-100" placeholder="Contoh: Gedung Atha, Jalan ...">{{ old('event_location') }}</textarea>
                <p class="mt-2 text-sm text-stone-500 dark:text-stone-400">Tulis nama gedung, alamat, rumah, atau lokasi outdoor.</p>
            </div>

            <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('packages.show', $weddingPackage) }}" class="rounded-full border border-stone-300 px-5 py-3 text-center text-sm font-semibold text-stone-700 hover:border-rose-700 hover:text-rose-700 dark:border-stone-600 dark:text-stone-200 dark:hover:border-rose-400 dark:hover:text-rose-300">Kembali</a>
                <button class="rounded-full bg-stone-900 px-6 py-3 text-sm font-semibold text-white hover:bg-rose-800 dark:bg-rose-700 dark:hover:bg-rose-600" type="submit">Review booking</button>
            </div>
        </form>
    </main>
@endsection
