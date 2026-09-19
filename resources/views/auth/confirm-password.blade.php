@extends('layouts.app')

@section('title', 'Konfirmasi Password — Atha Decoration')

@section('content')
    <main id="main-content" class="mx-auto max-w-md px-5 py-16 lg:py-24">
        <section class="rounded-3xl border border-stone-200 bg-white p-7 shadow-xl shadow-stone-900/5 dark:border-[#625752] dark:bg-[#302824] dark:shadow-black/30 sm:p-10">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-rose-700 dark:text-rose-300">Keamanan akun</p>
            <h1 class="mt-4 font-serif text-4xl text-stone-900 dark:text-stone-50">Konfirmasi password</h1>
            <p class="mt-4 leading-7 text-stone-600 dark:text-stone-300">Masukkan password Anda untuk melanjutkan ke halaman yang dilindungi.</p>

            <form method="POST" action="{{ route('password.confirm') }}" class="mt-8 space-y-5">
                @csrf
                <x-password-input id="password" label="Password saat ini" name="password" required autofocus autocomplete="current-password" />
                <button class="w-full rounded-full bg-stone-900 px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-rose-800 focus:outline-none focus:ring-2 focus:ring-rose-600 focus:ring-offset-2 dark:bg-rose-600 dark:hover:bg-rose-500 dark:focus:ring-rose-400 dark:focus:ring-offset-stone-900" type="submit">Konfirmasi</button>
            </form>
        </section>
    </main>
@endsection
