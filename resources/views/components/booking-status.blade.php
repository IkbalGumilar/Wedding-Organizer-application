@props(['status'])

<span @class([
    'inline-flex w-fit shrink-0 rounded-full px-3 py-1.5 text-sm font-semibold',
    'bg-yellow-100 text-yellow-800 dark:bg-amber-950/60 dark:text-amber-200' => $status === \App\Enums\BookingStatus::Pending,
    'bg-blue-100 text-blue-800 dark:bg-sky-950/60 dark:text-sky-200' => $status === \App\Enums\BookingStatus::Accepted,
    'bg-green-100 text-green-800 dark:bg-emerald-950/60 dark:text-emerald-200' => $status === \App\Enums\BookingStatus::Completed,
    'bg-red-100 text-red-800 dark:bg-rose-950/60 dark:text-rose-200' => $status === \App\Enums\BookingStatus::Cancelled,
])>{{ $status->label() }}</span>
