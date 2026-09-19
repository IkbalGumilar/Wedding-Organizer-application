<?php

namespace App\Http\Controllers;

use App\Actions\Bookings\CreateBooking;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\WeddingPackage;
use App\Services\BookingAvailabilityService;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        return view('customer.bookings.index', [
            'bookings' => $request->user()->bookings()->latest()->paginate(10),
        ]);
    }

    public function create(WeddingPackage $weddingPackage): View
    {
        abort_unless($weddingPackage->is_active, 404);

        return view('customer.bookings.create', compact('weddingPackage'));
    }

    public function store(StoreBookingRequest $request): RedirectResponse
    {
        $user = $request->user();

        if (blank($user->phone)) {
            $request->session()->put('booking.resume', $request->validated());

            return redirect()->route('profile.edit')->with('status', 'Nomor WhatsApp diperlukan untuk melanjutkan booking dan komunikasi terkait acara.');
        }

        $package = WeddingPackage::query()
            ->whereKey($request->validated('wedding_package_id'))
            ->where('is_active', true)
            ->firstOrFail();

        $termsSnapshot = $package->termsSnapshot();

        if (blank($termsSnapshot)) {
            throw ValidationException::withMessages([
                'wedding_package_id' => 'Syarat dan ketentuan paket belum tersedia. Silakan hubungi admin.',
            ]);
        }

        $request->session()->put('booking.review', [
            ...$request->validated(),
            'package_price' => $package->price,
            'package_version' => $package->snapshotVersion(),
            'terms_snapshot' => $termsSnapshot,
            'terms_version' => $package->termsVersion(),
        ]);

        return redirect()->route('bookings.review');
    }

    public function review(Request $request): View|RedirectResponse
    {
        $review = $request->session()->get('booking.review');

        if (! is_array($review)) {
            return redirect()->route('packages.index')->with('status', 'Isi data booking terlebih dahulu.');
        }

        $package = WeddingPackage::query()
            ->whereKey($review['wedding_package_id'] ?? null)
            ->where('is_active', true)
            ->first();

        if (! $package || $package->snapshotVersion() !== ($review['package_version'] ?? null) || $package->termsVersion() !== ($review['terms_version'] ?? null)) {
            $request->session()->forget('booking.review');

            return redirect()->route('packages.index')->withErrors([
                'wedding_package_id' => 'Paket atau ketentuannya berubah. Silakan mulai review booking dari awal.',
            ]);
        }

        return view('customer.bookings.review', [
            'review' => $review,
            'package' => $package,
        ]);
    }

    public function confirm(Request $request, CreateBooking $createBooking): RedirectResponse
    {
        if (blank($request->user()->phone)) {
            return redirect()->route('profile.edit')->with('status', 'Nomor WhatsApp diperlukan untuk melanjutkan booking dan komunikasi terkait acara.');
        }

        Validator::make($request->all(), [
            'terms_accepted' => ['required', 'accepted'],
        ])->validate();

        $rawReview = $request->session()->get('booking.review');

        if (! is_array($rawReview)) {
            return redirect()->route('packages.index')->withErrors([
                'booking' => 'Sesi review booking sudah berakhir. Silakan mulai kembali.',
            ]);
        }

        $review = Validator::make($rawReview, [
            'wedding_package_id' => ['required', 'integer'],
            'event_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'couple_name' => ['required', 'string', 'max:150'],
            'event_location' => ['required', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'package_price' => ['required', 'integer', 'min:0'],
            'package_version' => ['required', 'string', 'size:64'],
            'terms_snapshot' => ['required', 'string', 'max:10000'],
            'terms_version' => ['required', 'string', 'size:64'],
        ])->validate();

        try {
            $booking = $createBooking->handle($request->user(), $review);
        } catch (DomainException $exception) {
            if (str_contains($exception->getMessage(), 'penuh')) {
                return redirect()->route('bookings.review')->withErrors([
                    'event_date' => $exception->getMessage(),
                ]);
            }

            $request->session()->forget('booking.review');

            return redirect()->route('packages.index')->withErrors(['booking' => $exception->getMessage()]);
        }

        $request->session()->forget('booking.review');

        return redirect()->route('bookings.show', $booking)->with('status', 'Permintaan booking berhasil dikonfirmasi.');
    }

    public function availability(Request $request, BookingAvailabilityService $availability): JsonResponse
    {
        $month = Validator::make($request->all(), [
            'month' => ['nullable', 'date_format:Y-m'],
        ])->validate()['month'] ?? now()->format('Y-m');

        $date = CarbonImmutable::createFromFormat('Y-m', $month, config('app.timezone'))->startOfMonth();

        return response()->json([
            'month' => $date->format('Y-m'),
            'days' => $availability->forMonth($date),
        ]);
    }

    public function show(Request $request, Booking $booking): View
    {
        abort_unless($booking->user_id === $request->user()->id, 404);

        return view('customer.bookings.show', [
            'booking' => $booking,
        ]);
    }
}
