@extends('layouts.app')

@section('title', 'Profil Saya — Atha Decoration')

@section('content')
    @php
        $providers = config('auth.social_login_ui_enabled') ? \App\Enums\SocialProvider::enabled() : [];
        $connectedProviders = $user->socialAccounts->keyBy('provider');
        $showCompletionPrompt = session('social.profile.prompt') && blank($user->phone);
    @endphp

    <main id="main-content" class="mx-auto max-w-3xl px-5 py-12 lg:px-8 lg:py-20">
        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-rose-700 dark:text-rose-300">Akun</p>
        <h1 class="mt-4 font-serif text-5xl text-stone-900 dark:text-stone-50">Profil saya</h1>
        <p class="mt-4 text-stone-600 dark:text-stone-300">Kelola informasi kontak dan cara Anda masuk ke akun.</p>

        @if ($showCompletionPrompt)
            <section class="mt-8 rounded-3xl border border-rose-200 bg-rose-50 p-6 dark:border-rose-900/70 dark:bg-rose-950/30">
                <p class="text-sm font-semibold uppercase tracking-[0.16em] text-rose-800 dark:text-rose-200">Lengkapi Profil</p>
                <h2 class="mt-2 font-serif text-3xl text-stone-900 dark:text-stone-50">Nomor WhatsApp dapat diisi nanti</h2>
                <p class="mt-3 max-w-xl text-sm leading-6 text-stone-700 dark:text-stone-200">Anda tetap dapat melihat paket dan area pelanggan. Nomor WhatsApp diperlukan saat melanjutkan booking dan komunikasi terkait acara.</p>
                <form method="POST" action="{{ route('social.profile.skip') }}" class="mt-5">
                    @csrf
                    <button type="submit" class="rounded-full border border-rose-300 px-5 py-2.5 text-sm font-semibold text-rose-800 transition hover:bg-white/70 focus:outline-none focus:ring-2 focus:ring-rose-600 dark:border-rose-700 dark:text-rose-200 dark:hover:bg-stone-900/70 dark:focus:ring-rose-400">Lewati dulu</button>
                </form>
            </section>
        @endif

        <form method="POST" action="{{ route('profile.update') }}" class="mt-10 rounded-3xl border border-stone-200 bg-white p-7 shadow-xl shadow-stone-900/5 dark:border-[#625752] dark:bg-[#302824] dark:shadow-black/30 sm:p-10">
            @csrf
            @method('PUT')

            <div class="space-y-5">
                <div>
                    <label for="name" class="text-sm font-semibold text-stone-700 dark:text-stone-200">Nama lengkap</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autocomplete="name" class="mt-2 w-full rounded-xl border border-stone-300 bg-stone-50 px-4 py-3 text-stone-900 placeholder:text-stone-400 focus:border-rose-600 focus:ring-rose-600 dark:border-[#625752] dark:bg-[#3b302c] dark:text-stone-100 dark:placeholder:text-stone-500 dark:focus:border-rose-400 dark:focus:ring-rose-400">
                </div>
                <div>
                    <label for="email" class="text-sm font-semibold text-stone-700 dark:text-stone-200">Email @unless($user->hasPassword())<span class="font-normal text-stone-400 dark:text-stone-500">(opsional)</span>@endunless</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" @if($user->hasPassword()) required @endif autocomplete="email" class="mt-2 w-full rounded-xl border border-stone-300 bg-stone-50 px-4 py-3 text-stone-900 placeholder:text-stone-400 focus:border-rose-600 focus:ring-rose-600 dark:border-[#625752] dark:bg-[#3b302c] dark:text-stone-100 dark:placeholder:text-stone-500 dark:focus:border-rose-400 dark:focus:ring-rose-400">
                </div>
                <div>
                    <label for="phone" class="text-sm font-semibold text-stone-700 dark:text-stone-200">Nomor WhatsApp <span class="font-normal text-stone-400 dark:text-stone-500">(opsional, wajib sebelum booking)</span></label>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" autocomplete="tel" class="mt-2 w-full rounded-xl border border-stone-300 bg-stone-50 px-4 py-3 text-stone-900 placeholder:text-stone-400 focus:border-rose-600 focus:ring-rose-600 dark:border-[#625752] dark:bg-[#3b302c] dark:text-stone-100 dark:placeholder:text-stone-500 dark:focus:border-rose-400 dark:focus:ring-rose-400">
                </div>
            </div>

            <button class="mt-8 rounded-full bg-stone-900 px-6 py-3 text-sm font-semibold text-white transition hover:bg-rose-800 focus:outline-none focus:ring-2 focus:ring-rose-600 focus:ring-offset-2 dark:bg-rose-600 dark:hover:bg-rose-500 dark:focus:ring-rose-400 dark:focus:ring-offset-stone-900" type="submit">Simpan profil</button>
        </form>

        @if (filled($user->email))
            <form method="POST" action="{{ route('profile.password') }}" class="mt-6 rounded-3xl border border-stone-200 bg-white p-7 shadow-xl shadow-stone-900/5 dark:border-[#625752] dark:bg-[#302824] dark:shadow-black/30 sm:p-10">
                @csrf
                @method('PUT')

                <h2 class="font-serif text-3xl text-stone-900 dark:text-stone-50">{{ $user->hasPassword() ? 'Ganti password' : 'Buat password' }}</h2>
                @unless ($user->hasPassword())
                    <p class="mt-3 text-sm leading-6 text-stone-600 dark:text-stone-300">Setelah dibuat, Anda juga dapat masuk memakai email dan password ini.</p>
                @endunless

                <div class="mt-6 space-y-5">
                    @if ($user->hasPassword())
                        <x-password-input id="current_password" label="Password saat ini" name="current_password" required autocomplete="current-password" />
                    @endif
                    <x-password-input id="new_password" label="Password baru" name="password" required autocomplete="new-password" />
                    <x-password-input id="new_password_confirmation" label="Ulangi password" name="password_confirmation" required autocomplete="new-password" />
                </div>

                <button class="mt-8 rounded-full border border-stone-900 px-6 py-3 text-sm font-semibold text-stone-900 transition hover:bg-stone-900 hover:text-white focus:outline-none focus:ring-2 focus:ring-rose-600 focus:ring-offset-2 dark:border-stone-500 dark:text-stone-100 dark:hover:bg-stone-800 dark:focus:ring-rose-400 dark:focus:ring-offset-stone-900" type="submit">{{ $user->hasPassword() ? 'Perbarui password' : 'Buat password' }}</button>
            </form>
        @else
            <section class="mt-6 rounded-3xl border border-stone-200 bg-white p-7 dark:border-[#625752] dark:bg-[#302824]">
                <h2 class="font-serif text-3xl text-stone-900 dark:text-stone-50">Buat password</h2>
                <p class="mt-3 text-sm leading-6 text-stone-600 dark:text-stone-300">Tambahkan email terlebih dahulu jika Anda ingin masuk menggunakan email dan password.</p>
            </section>
        @endif

        @if (count($providers))
            <section class="mt-6 rounded-3xl border border-stone-200 bg-white p-7 shadow-xl shadow-stone-900/5 dark:border-[#625752] dark:bg-[#302824] dark:shadow-black/30 sm:p-10">
                <p class="text-sm font-semibold uppercase tracking-[0.16em] text-rose-700 dark:text-rose-300">Akses akun</p>
                <h2 class="mt-3 font-serif text-3xl text-stone-900 dark:text-stone-50">Akun sosial terhubung</h2>
                <p class="mt-3 text-sm leading-6 text-stone-600 dark:text-stone-300">Hubungkan provider agar Anda dapat masuk dengan cara yang paling nyaman.</p>

                <div class="mt-6 divide-y divide-stone-200 rounded-2xl border border-stone-200 dark:divide-stone-700 dark:border-stone-700">
                    @foreach ($providers as $provider)
                        <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-semibold text-stone-800 dark:text-stone-100">{{ $provider->label() }}</p>
                                <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">{{ $connectedProviders->has($provider->value) ? 'Sudah terhubung' : 'Belum terhubung' }}</p>
                            </div>
                            @if ($connectedProviders->has($provider->value))
                                <span class="w-fit rounded-full bg-emerald-100 px-3 py-1.5 text-sm font-semibold text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-200">Terhubung</span>
                            @elseif (! $provider->canLink())
                                <span class="text-sm text-stone-500 dark:text-stone-400">Hubungkan belum tersedia</span>
                            @else
                                <a href="{{ route('social.redirect', $provider->value) }}" class="w-fit rounded-full border border-stone-300 px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-rose-600 hover:text-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-600 dark:border-stone-600 dark:text-stone-200 dark:hover:border-rose-400 dark:hover:text-rose-300 dark:focus:ring-rose-400">Hubungkan</a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </main>
@endsection
