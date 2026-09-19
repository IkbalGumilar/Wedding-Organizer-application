<?php

namespace App\Http\Controllers;

use App\Models\WeddingPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('customer.profile', ['user' => $request->user()->loadMissing('socialAccounts')]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (is_string($request->input('email'))) {
            $request->merge(['email' => Str::lower(trim($request->input('email')))]);
        }

        $validated = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:150'],
            'email' => [Rule::requiredIf($user->hasPassword()), 'nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:32'],
        ])->validate();

        $email = filled($validated['email'] ?? null) ? mb_strtolower($validated['email']) : null;
        $attributes = [
            'name' => $validated['name'],
            'email' => $email,
            'phone' => $validated['phone'] ?? null,
        ];

        if ($email !== $user->email) {
            $attributes['email_verified_at'] = null;
        }

        $user->forceFill($attributes)->save();

        if (filled($user->phone)) {
            $request->session()->forget('social.profile.prompt');

            if (is_array($request->session()->get('booking.review'))) {
                return redirect()->route('bookings.review')->with('status', 'Profil berhasil diperbarui. Lanjutkan konfirmasi booking Anda.');
            }

            $resume = $request->session()->pull('booking.resume');

            if (is_array($resume)) {
                $package = WeddingPackage::query()
                    ->whereKey($resume['wedding_package_id'] ?? null)
                    ->where('is_active', true)
                    ->first();

                if ($package) {
                    $request->session()->flashInput($resume);

                    return redirect()->route('bookings.create', $package)->with('status', 'Profil berhasil diperbarui. Lanjutkan pengajuan booking Anda.');
                }

                return redirect()->route('packages.index')->withErrors([
                    'wedding_package_id' => 'Paket yang dipilih sudah tidak tersedia. Silakan pilih paket lain.',
                ]);
            }
        }

        return back()->with('status', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (blank($user->email)) {
            return back()->withErrors([
                'email' => 'Tambahkan email sebelum membuat password untuk login dengan email.',
            ]);
        }

        $rules = [
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ];

        if ($user->hasPassword()) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        $validated = Validator::make($request->all(), $rules)->validate();

        $user->forceFill([
            'password' => Hash::make($validated['password']),
            'remember_token' => Str::random(60),
        ])->save();

        return back()->with('status', 'Password berhasil diperbarui.');
    }
}
