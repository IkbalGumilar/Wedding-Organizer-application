@extends('layouts.app')

@section('title', 'Masuk — Atha Decoration')

@section('content')
    <main id="main-content" class="mx-auto max-w-md px-5 py-16 lg:py-24">
        <section class="rounded-3xl border border-stone-200 bg-white p-7 shadow-xl shadow-stone-900/5 dark:border-[#625752] dark:bg-[#302824] dark:shadow-black/30 sm:p-10">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-rose-700 dark:text-rose-300">Selamat datang kembali</p>
            <h1 class="mt-4 font-serif text-4xl text-stone-900 dark:text-stone-50">Masuk ke akun Anda</h1>
            <p class="mt-3 text-sm leading-6 text-stone-600 dark:text-stone-300">Lanjutkan dengan akun sosial atau email Anda.</p>

            <div class="mt-8">
                <x-social-login-buttons />
            </div>

            <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="text-sm font-semibold text-stone-700 dark:text-stone-200">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="mt-2 w-full rounded-xl border border-stone-300 bg-stone-50 px-4 py-3 text-stone-900 placeholder:text-stone-400 focus:border-rose-600 focus:ring-rose-600 disabled:cursor-not-allowed disabled:opacity-60 dark:border-[#625752] dark:bg-[#3b302c] dark:text-stone-100 dark:placeholder:text-stone-500 dark:focus:border-rose-400 dark:focus:ring-rose-400">
                </div>
                <div>
                    <x-password-input id="password" label="Password" name="password" required autofocus autocomplete="current-password" class="disabled:cursor-not-allowed disabled:opacity-60">
                        <x-slot:labelAction>
                            <a href="{{ route('password.request') }}" class="text-xs font-semibold text-rose-700 hover:text-rose-900 dark:text-rose-300 dark:hover:text-rose-200">Lupa password?</a>
                        </x-slot:labelAction>
                    </x-password-input>
                </div>
                <label class="flex items-center gap-2 text-sm text-stone-600 dark:text-stone-300">
                    <input name="remember" type="checkbox" class="rounded border-stone-300 text-rose-700 focus:ring-rose-600 dark:border-[#625752] dark:bg-[#3b302c] dark:text-rose-400 dark:focus:ring-rose-400">
                    Ingat saya
                </label>
                <button class="w-full rounded-full bg-stone-900 px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-rose-800 focus:outline-none focus:ring-2 focus:ring-rose-600 focus:ring-offset-2 dark:bg-rose-600 dark:hover:bg-rose-500 dark:focus:ring-rose-400 dark:focus:ring-offset-stone-900" type="submit">Masuk</button>
            </form>

            <p class="mt-7 text-center text-sm text-stone-600 dark:text-stone-300">Belum punya akun? <a href="{{ route('register') }}" class="font-semibold text-rose-700 hover:text-rose-900 dark:text-rose-300 dark:hover:text-rose-200">Daftar sekarang</a></p>
        </section>
    </main>
@endsection
