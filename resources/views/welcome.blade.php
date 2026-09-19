<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex, nofollow">
        <title>{{ config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-stone-50 text-stone-800 antialiased">
        <main class="mx-auto flex min-h-screen max-w-3xl flex-col justify-center px-6 py-16 text-center">
            <p class="mb-6 text-sm font-medium uppercase tracking-[0.2em] text-stone-600">Wedding &amp; Decoration</p>
            <h1 class="font-serif text-5xl leading-tight sm:text-7xl">{{ config('app.name') }}</h1>
            <p class="mx-auto mt-8 max-w-md text-lg leading-relaxed text-stone-600">
                Ruang untuk cerita indah Anda sedang kami persiapkan.
            </p>
        </main>
    </body>
</html>
