@extends('layouts.app')

@section('title', 'Hubungkan Apple — Atha Decoration')

@section('content')
    <main id="main-content" class="mx-auto max-w-md px-5 py-16 lg:py-24">
        <section class="rounded-3xl border border-stone-200 bg-white p-7 shadow-xl shadow-stone-900/5 dark:border-[#625752] dark:bg-[#302824] dark:shadow-black/30 sm:p-10">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-rose-700 dark:text-rose-300">Keamanan akun</p>
            <h1 class="mt-4 font-serif text-4xl text-stone-900 dark:text-stone-50">Hubungkan akun Apple?</h1>
            <p class="mt-4 text-sm leading-7 text-stone-600 dark:text-stone-300">Apple sudah memverifikasi identitas Anda. Konfirmasikan untuk menghubungkannya ke akun Atha Decoration yang sedang aktif.</p>

            <form method="POST" action="{{ route('social.apple-link.confirm') }}" class="mt-8 flex flex-col gap-3 sm:flex-row">
                @csrf
                <button type="submit" class="rounded-full bg-stone-900 px-6 py-3 text-sm font-semibold text-white transition hover:bg-rose-800 focus:outline-none focus:ring-2 focus:ring-rose-600 focus:ring-offset-2 dark:bg-rose-600 dark:hover:bg-rose-500 dark:focus:ring-rose-400 dark:focus:ring-offset-[#302824]">Hubungkan Apple</button>
                <a href="{{ route('profile.edit') }}" class="rounded-full border border-stone-300 px-6 py-3 text-center text-sm font-semibold text-stone-700 transition hover:border-rose-600 hover:text-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-600 focus:ring-offset-2 dark:border-[#625752] dark:text-stone-100 dark:hover:border-rose-400 dark:hover:text-rose-300 dark:focus:ring-rose-400 dark:focus:ring-offset-[#302824]">Batal</a>
            </form>
        </section>
    </main>
@endsection
