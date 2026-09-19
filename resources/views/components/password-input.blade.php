@props([
    'id',
    'label',
    'name' => null,
])

<div>
    @isset($labelAction)
        <div class="flex items-center justify-between gap-4">
            <label for="{{ $id }}" class="text-sm font-semibold text-stone-700 dark:text-stone-200">{{ $label }}</label>
            {{ $labelAction }}
        </div>
    @else
        <label for="{{ $id }}" class="text-sm font-semibold text-stone-700 dark:text-stone-200">{{ $label }}</label>
    @endisset
    <div class="relative mt-2">
        <input
            id="{{ $id }}"
            name="{{ $name ?? $id }}"
            type="password"
            {{ $attributes->merge(['class' => 'block w-full rounded-xl border border-stone-300 bg-stone-50 px-4 py-3 pr-12 text-stone-900 placeholder:text-stone-400 focus:border-rose-600 focus:ring-rose-600 dark:border-[#625752] dark:bg-[#3b302c] dark:text-stone-100 dark:placeholder:text-stone-500 dark:focus:border-rose-400 dark:focus:ring-rose-400']) }}
        >
        <button
            type="button"
            data-password-toggle
            data-password-toggle-target="{{ $id }}"
            aria-controls="{{ $id }}"
            aria-label="Tampilkan kata sandi"
            aria-pressed="false"
            title="Tampilkan kata sandi"
            class="absolute inset-y-0 right-0 flex w-12 items-center justify-center rounded-r-xl text-stone-500 transition hover:text-rose-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-rose-600 dark:text-stone-300 dark:hover:text-rose-300 dark:focus:ring-rose-400"
        >
            <svg data-password-show-icon xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-6 9.75-6 9.75 6 9.75 6-3.5 6-9.75 6-9.75-6-9.75-6Z" />
                <circle cx="12" cy="12" r="2.75" />
            </svg>
            <svg data-password-hide-icon xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="hidden h-5 w-5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.58 10.58a2 2 0 0 0 2.83 2.83M9.88 5.08A10.8 10.8 0 0 1 12 4.88c6.25 0 9.75 6 9.75 6a17.4 17.4 0 0 1-3.17 3.88M6.23 6.23C3.75 7.72 2.25 10.88 2.25 10.88s3.5 6 9.75 6a10.7 10.7 0 0 0 3.28-.51" />
            </svg>
        </button>
    </div>
</div>
