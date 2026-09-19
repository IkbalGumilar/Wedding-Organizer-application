@extends('layouts.app')

@section('title', 'Atur Ulang Password — Atha Decoration')

@section('content')
    <main id="main-content" class="mx-auto max-w-md px-5 py-16 lg:py-24">
        <section class="rounded-3xl border border-stone-200 bg-white p-7 shadow-xl shadow-stone-900/5 dark:border-[#625752] dark:bg-[#302824] dark:shadow-black/30 sm:p-10">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-rose-700 dark:text-rose-300">Akses akun</p>
            <h1 class="mt-4 font-serif text-4xl text-stone-900 dark:text-stone-50">Atur ulang password</h1>
            <p class="mt-4 text-sm leading-6 text-stone-600 dark:text-stone-300">Masukkan email Anda. Jika terdaftar, kami akan mengirimkan tautan untuk membuat password baru.</p>

            <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-5">
                @csrf
                <div>
                    <label for="email" class="text-sm font-semibold text-stone-700 dark:text-stone-200">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="mt-2 w-full rounded-xl border border-stone-300 bg-stone-50 px-4 py-3 text-stone-900 placeholder:text-stone-400 focus:border-rose-600 focus:ring-rose-600 dark:border-[#625752] dark:bg-[#3b302c] dark:text-stone-100 dark:placeholder:text-stone-500 dark:focus:border-rose-400 dark:focus:ring-rose-400">
                </div>
                <button class="w-full rounded-full bg-stone-900 px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-rose-800 focus:outline-none focus:ring-2 focus:ring-rose-600 focus:ring-offset-2 dark:bg-rose-600 dark:hover:bg-rose-500 dark:focus:ring-rose-400 dark:focus:ring-offset-stone-900" type="submit">Kirim tautan reset</button>
            </form>

            <p class="mt-7 text-center text-sm"><a href="{{ route('login') }}" class="font-semibold text-rose-700 hover:text-rose-900 dark:text-rose-300 dark:hover:text-rose-200">← Kembali ke login</a></p>
        </section>
    </main>
@endsection
