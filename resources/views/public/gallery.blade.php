@extends('layouts.app')

@section('title', 'Gallery — Atha Decoration')

@section('content')
    <main id="main-content" class="mx-auto max-w-7xl px-5 py-16 lg:px-8 lg:py-24">
        <div class="max-w-2xl">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-rose-700 dark:text-rose-300">Gallery / portfolio</p>
            <h1 class="mt-5 font-serif text-5xl leading-tight text-stone-900 dark:text-stone-50">Cerita di setiap detail.</h1>
            <p class="mt-6 text-lg leading-8 text-stone-600 dark:text-stone-300">Jelajahi momen dan dekorasi dalam portfolio Atha Decoration.</p>
        </div>

        <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($galleries as $gallery)
                <figure class="overflow-hidden rounded-3xl border border-stone-200 bg-white dark:border-[#625752] dark:bg-[#302824]">
                    <div class="aspect-[4/3] overflow-hidden bg-rose-100 dark:bg-rose-950/50">
                        <img src="{{ Storage::disk('public')->url($gallery->image_path) }}" alt="{{ $gallery->title }}" class="h-full w-full object-cover" loading="lazy">
                    </div>
                    <figcaption class="p-5">
                        <h2 class="break-words font-serif text-2xl text-stone-900 dark:text-stone-50">{{ $gallery->title }}</h2>
                        @if ($gallery->description)
                            <p class="mt-2 break-words text-sm leading-6 text-stone-600 dark:text-stone-300">{{ $gallery->description }}</p>
                        @endif
                    </figcaption>
                </figure>
            @empty
                <div class="rounded-3xl border border-dashed border-stone-300 p-10 text-stone-600 dark:border-stone-600 dark:text-stone-300 sm:col-span-2 lg:col-span-3">Portfolio kami sedang dipersiapkan.</div>
            @endforelse
        </div>

        <div class="mt-10">{{ $galleries->links() }}</div>
    </main>
@endsection
