@props(['sections' => []])

@php
    $visibleSections = collect($sections ?? [])->filter(function ($section): bool {
        return is_array($section)
            && filled($section['title'] ?? null)
            && collect($section['items'] ?? [])->filter(fn ($item): bool => filled($item))->isNotEmpty();
    });
@endphp

@if ($visibleSections->isNotEmpty())
    <div {{ $attributes->class(['space-y-8']) }}>
        @foreach ($visibleSections as $section)
            <section>
                <h2 class="font-serif text-3xl text-stone-900 dark:text-stone-50">{{ $section['title'] }}</h2>
                <ul class="mt-4 space-y-3 text-sm leading-7 text-stone-600 dark:text-stone-300">
                    @foreach (collect($section['items'] ?? [])->filter(fn ($item): bool => filled($item)) as $item)
                        <li class="flex gap-3">
                            <span class="mt-3 h-1.5 w-1.5 shrink-0 rounded-full bg-rose-600 dark:bg-rose-400" aria-hidden="true"></span>
                            <span class="break-words">{{ $item }}</span>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endforeach
    </div>
@endif
