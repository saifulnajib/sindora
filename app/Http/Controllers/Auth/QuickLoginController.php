<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class QuickLoginController extends Controller
{
    /**
     * Quick login for demo — only allowed in non-production or when explicitly enabled.
     * Uses email only (no password) to speed up Sprint 1 demo.
     */
    public function store(Request $request): RedirectResponse
    {
        // Guard: quick login is only allowed in local environment
        if (! app()->environment('local')) {
            abort(403, 'Quick login hanya diizinkan di environment local.');
        }

        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $user = User::where('email', $request->input('email'))->first();

        if (! $user) {
            throw ValidationException::withMessages(['email' => 'Akun tidak ditemukan']);
        }

        // Optional: only allow demo accounts
        $allowed = [
            'superadmin@sindora.test',
            'verifikator@sindora.test',
            'org@sindora.test',
            'klub@sindora.test',
            'pimpinan@sindora.test',
        ];
        if (! in_array($user->email, $allowed, true)) {
            abort(403, 'Quick login hanya untuk akun demo');
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
