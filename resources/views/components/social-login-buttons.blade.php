@props(['providers' => null, 'divider' => true])

@php
    $providers ??= \App\Enums\SocialProvider::enabled();
@endphp

@if (config('auth.social_login_ui_enabled') && count($providers))
    <div class="mt-8 grid grid-cols-1 gap-3 sm:grid-cols-2" aria-label="Masuk dengan akun sosial">
        @foreach ($providers as $provider)
            <a
                href="{{ route('social.redirect', $provider->value) }}"
                data-social-provider="{{ $provider->value }}"
                aria-label="Lanjutkan dengan {{ $provider->label() }}"
                class="group relative flex min-h-12 w-full items-center justify-center rounded-xl border border-stone-300 bg-white px-12 py-3 text-center text-sm font-semibold leading-5 text-stone-800 transition hover:border-rose-400 hover:bg-rose-50 focus:outline-none focus:ring-2 focus:ring-rose-600 focus:ring-offset-2 dark:border-[#625752] dark:bg-[#3b302c] dark:text-stone-100 dark:hover:border-rose-400 dark:hover:bg-rose-950/30 dark:focus:ring-offset-[#302824]"
            >
                <span class="absolute left-4 grid h-6 w-6 shrink-0 place-items-center" aria-hidden="true">
                    @switch($provider)
                        @case(\App\Enums\SocialProvider::Google)
                            <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none">
                                <path fill="#4285F4" d="M21.6 12.23c0-.71-.06-1.39-.18-2.05H12v3.88h5.38a4.6 4.6 0 0 1-1.99 3.02v2.51h3.23c1.89-1.74 2.98-4.3 2.98-7.36Z"/>
                                <path fill="#34A853" d="M12 22c2.7 0 4.96-.9 6.62-2.41l-3.23-2.51c-.9.6-2.05.96-3.39.96-2.61 0-4.83-1.76-5.62-4.13H3.04v2.59A10 10 0 0 0 12 22Z"/>
                                <path fill="#FBBC05" d="M6.38 13.91A6.01 6.01 0 0 1 6.07 12c0-.66.11-1.3.31-1.91V7.5H3.04A10 10 0 0 0 2 12c0 1.61.39 3.14 1.04 4.5l3.34-2.59Z"/>
                                <path fill="#EA4335" d="M12 5.96c1.47 0 2.79.51 3.83 1.51l2.87-2.87C16.95 2.96 14.7 2 12 2a10 10 0 0 0-8.96 5.5l3.34 2.59C7.17 7.72 9.39 5.96 12 5.96Z"/>
                            </svg>
                            @break
                        @case(\App\Enums\SocialProvider::Apple)
                            <svg viewBox="0 0 24 24" class="h-6 w-6 fill-current" aria-hidden="true"><path d="M16.71 12.68c.02-1.7 1.39-2.52 1.45-2.56-.79-1.16-2.01-1.32-2.44-1.34-1.04-.1-2.04.61-2.57.61-.53 0-1.35-.6-2.22-.58-1.14.02-2.2.66-2.79 1.68-1.2 2.08-.31 5.16.86 6.85.57.83 1.25 1.76 2.14 1.73.86-.03 1.18-.55 2.22-.55s1.33.55 2.24.53c.92-.02 1.51-.84 2.08-1.67.66-.96.93-1.88.95-1.93-.02-.01-1.81-.7-1.79-2.77Zm-1.7-4.98c.48-.58.8-1.39.71-2.2-.69.03-1.53.46-2.03 1.04-.45.52-.84 1.35-.73 2.14.77.06 1.56-.39 2.05-.98Z"/></svg>
                            @break
                        @case(\App\Enums\SocialProvider::Microsoft)
                            <svg viewBox="0 0 24 24" class="h-6 w-6" aria-hidden="true"><path fill="#f25022" d="M2 2h9.5v9.5H2z"/><path fill="#7fba00" d="M12.5 2H22v9.5h-9.5z"/><path fill="#00a4ef" d="M2 12.5h9.5V22H2z"/><path fill="#ffb900" d="M12.5 12.5H22V22h-9.5z"/></svg>
                            @break
                        @case(\App\Enums\SocialProvider::Facebook)
                            <svg viewBox="0 0 24 24" class="h-6 w-6 fill-[#1877f2]" aria-hidden="true"><path d="M24 12.07C24 5.4 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.05V9.41c0-3.03 1.79-4.7 4.54-4.7 1.31 0 2.69.24 2.69.24v2.97h-1.52c-1.5 0-1.97.94-1.97 1.9v2.25h3.35l-.54 3.49h-2.81V24C19.61 23.1 24 18.1 24 12.07Z"/></svg>
                            @break
                        @case(\App\Enums\SocialProvider::X)
                            <svg viewBox="0 0 24 24" class="h-6 w-6 fill-current" aria-hidden="true"><path d="M18.9 2H22l-6.77 7.74L23.2 22h-6.24l-4.89-7.57L5.45 22H2.34l7.24-8.27L1.93 2h6.4l4.42 6.89L18.9 2Zm-1.09 18h1.72L7.4 3.9H5.55L17.81 20Z"/></svg>
                    @endswitch
                </span>
                <span class="whitespace-nowrap">Lanjutkan dengan {{ $provider->label() }}</span>
            </a>
        @endforeach
    </div>

    @if ($divider)
        <div class="my-8 flex items-center gap-4" aria-hidden="true">
            <span class="h-px flex-1 bg-stone-200 dark:bg-stone-700"></span>
            <span class="text-xs font-semibold uppercase tracking-[0.18em] text-stone-400 dark:text-stone-500">atau</span>
            <span class="h-px flex-1 bg-stone-200 dark:bg-stone-700"></span>
        </div>
    @endif
@endif
