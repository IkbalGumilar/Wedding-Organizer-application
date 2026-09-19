@props(['package'])

<a href="{{ route('packages.show', $package) }}" class="group flex h-full flex-col overflow-hidden rounded-3xl border border-stone-200 bg-white transition hover:border-rose-300 hover:shadow-xl hover:shadow-rose-900/5 dark:border-[#625752] dark:bg-[#302824] dark:hover:border-rose-600 dark:hover:shadow-black/30">
    @if ($package->image_path)
        <img
            src="{{ Storage::disk('public')->url($package->image_path) }}"
            alt="{{ $package->name }}"
            class="aspect-[4/3] w-full object-cover"
            loading="lazy"
        >
    @endif

    <div class="flex flex-1 flex-col p-7">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-stone-500 dark:text-stone-400">Paket wedding</p>
        <h2 class="mt-4 break-words font-serif text-3xl text-stone-900 dark:text-stone-50">{{ $package->name }}</h2>
        @if ($package->tagline)
            <p class="mt-3 text-xs font-semibold uppercase tracking-[0.14em] text-rose-700 dark:text-rose-300">{{ $package->tagline }}</p>
        @endif
        <p class="mt-4 line-clamp-4 text-sm leading-6 text-stone-600 dark:text-stone-300">{{ $package->description }}</p>
        <div class="mt-auto flex items-center justify-between gap-3 pt-8">
            <span class="font-semibold text-rose-700 dark:text-rose-300">{{ $package->formatted_price }}</span>
            <span class="text-lg text-rose-700 dark:text-rose-300" aria-hidden="true">→</span>
        </div>
    </div>
</a>
