<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Atha Decoration — wedding organizer dan dekorasi untuk hari istimewa Anda.">
        <title>@yield('title', config('app.name'))</title>
        <script>
            (() => {
                const storedTheme = window.localStorage.getItem('atha-theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                document.documentElement.classList.toggle('dark', storedTheme ? storedTheme === 'dark' : prefersDark);
            })();
        </script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#fbf8f4] text-stone-800 antialiased dark:bg-[#191615] dark:text-[#fbf5ef]">
        <a href="#main-content" class="sr-only rounded-lg bg-white px-4 py-3 text-rose-800 focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 dark:bg-[#302824] dark:text-rose-200">Langsung ke konten</a>
        <header class="border-b border-stone-200/80 bg-[#fbf8f4]/95 backdrop-blur dark:border-[#4e4540] dark:bg-[#120f0e]/95">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-5 py-5 lg:px-8">
                <a href="{{ route('home') }}" class="font-serif text-xl tracking-tight text-stone-900 dark:text-stone-50 sm:text-2xl">Atha <span class="text-rose-700 dark:text-rose-300">Decoration</span></a>
                <nav aria-label="Navigasi utama" class="hidden items-center gap-7 text-sm font-medium text-stone-600 dark:text-stone-300 xl:flex">
                    <a class="transition hover:text-rose-700 dark:hover:text-rose-300" href="{{ route('about') }}">Tentang Kami</a>
                    <a class="transition hover:text-rose-700 dark:hover:text-rose-300" href="{{ route('packages.index') }}">Paket Wedding</a>
                    <a class="transition hover:text-rose-700 dark:hover:text-rose-300" href="{{ route('gallery.index') }}">Gallery</a>
                    <a class="transition hover:text-rose-700 dark:hover:text-rose-300" href="{{ route('contact') }}">Kontak</a>
                </nav>
                <div class="flex items-center gap-3 text-sm">
                    <button type="button" data-theme-toggle aria-pressed="false" aria-label="Aktifkan tema gelap" title="Aktifkan tema gelap" class="rounded-full border border-stone-300 p-2 text-stone-700 transition hover:border-rose-700 hover:text-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-600 dark:border-stone-600 dark:text-stone-200 dark:hover:border-rose-300 dark:hover:text-rose-200">
                        <svg data-theme-moon xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z" /></svg>
                        <svg data-theme-sun xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="hidden h-5 w-5" aria-hidden="true"><circle cx="12" cy="12" r="4" /><path stroke-linecap="round" d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42" /></svg>
                    </button>
                    @auth
                        <a href="{{ route('dashboard') }}" class="hidden font-medium text-stone-600 hover:text-rose-700 dark:text-stone-300 dark:hover:text-rose-300 xl:inline">Booking saya</a>
                        <a href="{{ route('profile.edit') }}" class="hidden font-medium text-stone-600 hover:text-rose-700 dark:text-stone-300 dark:hover:text-rose-300 xl:inline">Profil</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="rounded-full border border-stone-300 px-4 py-2 font-medium text-stone-700 transition hover:border-rose-700 hover:text-rose-700 dark:border-stone-600 dark:text-stone-200 dark:hover:border-rose-300 dark:hover:text-rose-200" type="submit">Keluar</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="hidden font-medium text-stone-600 hover:text-rose-700 dark:text-stone-300 dark:hover:text-rose-300 sm:inline">Masuk</a>
                        <a href="{{ route('register') }}" class="rounded-full bg-stone-900 px-4 py-2 font-medium text-white transition hover:bg-rose-800 dark:bg-rose-700 dark:hover:bg-rose-600">Mulai</a>
                    @endauth
                </div>
            </div>
            <nav aria-label="Navigasi seluler dan tablet" class="mx-auto flex max-w-7xl flex-wrap gap-x-5 gap-y-3 px-5 pb-4 text-xs font-semibold uppercase tracking-[0.12em] text-stone-600 dark:text-stone-300 xl:hidden">
                <a class="whitespace-nowrap" href="{{ route('about') }}">Tentang</a>
                <a class="whitespace-nowrap" href="{{ route('packages.index') }}">Paket</a>
                <a class="whitespace-nowrap" href="{{ route('gallery.index') }}">Gallery</a>
                <a class="whitespace-nowrap" href="{{ route('contact') }}">Kontak</a>
                @auth
                    <a class="whitespace-nowrap text-rose-700 dark:text-rose-300" href="{{ route('dashboard') }}">Booking saya</a>
                    <a class="whitespace-nowrap text-rose-700 dark:text-rose-300" href="{{ route('profile.edit') }}">Profil</a>
                @else
                    <a class="whitespace-nowrap text-rose-700 dark:text-rose-300 sm:hidden" href="{{ route('login') }}">Masuk</a>
                @endauth
            </nav>
        </header>

        @if (session('status'))
            <div class="mx-auto max-w-7xl px-5 pt-6 lg:px-8">
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200" role="status">{{ session('status') }}</div>
            </div>
        @endif

        @if ($errors->any())
            <div class="mx-auto max-w-7xl px-5 pt-6 lg:px-8">
                <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-800 dark:border-rose-800 dark:bg-rose-950/40 dark:text-rose-200" role="alert">
                    <p class="font-semibold">Periksa kembali data Anda.</p>
                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @yield('content')

        <footer class="mt-20 border-t border-stone-200 bg-white dark:border-[#4e4540] dark:bg-[#1f1a18]">
            <div class="mx-auto flex max-w-7xl flex-col gap-5 px-5 py-10 text-sm text-stone-500 dark:text-stone-400 sm:flex-row sm:items-center sm:justify-between lg:px-8">
                <p>© {{ date('Y') }} Atha Decoration. Dibuat untuk momen yang berarti.</p>
                @if (filled(config('services.whatsapp.number')))
                    <a class="font-semibold text-rose-700 hover:text-rose-900 dark:text-rose-300 dark:hover:text-rose-200" href="https://wa.me/{{ config('services.whatsapp.number') }}" target="_blank" rel="noopener noreferrer">Chat via WhatsApp →</a>
                @else
                    <a class="font-semibold text-rose-700 hover:text-rose-900 dark:text-rose-300 dark:hover:text-rose-200" href="{{ route('contact') }}">Informasi kontak →</a>
                @endif
            </div>
        </footer>
    </body>
</html>
