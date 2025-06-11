<?php

use App\Models\User;
use Harryes\SentinelLog\Services\SsoAuthenticationService;
use Harryes\SentinelLog\Services\TwoFactorAuthenticationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => view('welcome'));

Route::get('/login', fn() => view('login'))->name('login');
Route::post('/login', function () {
    if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
        return redirect()->route('dashboard');
    }
    return back()->withErrors(['email' => 'Invalid credentials']);
});

Route::get('/2fa/verify', fn() => view('2fa_verify'))->name('2fa.verify');
Route::post('/2fa/verify', function (TwoFactorAuthenticationService $service) {
    if ($service->verifyCode(Auth::user()->two_factor_secret, request('code'))) {
        session(['2fa_verified' => true]);
        return redirect()->route('dashboard');
    }
    return back()->withErrors(['code' => 'Invalid 2FA code']);
});

Route::match(['GET', 'POST'], '/sso/login', function (SsoAuthenticationService $ssoService) {
    if (Auth::check()) {
        return redirect()->route('dashboard'); // Avoid re-login if already authenticated
    }

    $token = request('sso_token');
    if (!$token) {
        return view('sso_login');
    }

    $user = $ssoService->validateToken($token, config('sentinel-log.sso.client_id', 'test_client'));
    if ($user) {
        Auth::login($user); // Explicit login here
        return redirect()->route('dashboard')->with('success', 'Logged in via SSO successfully!');
    }

    return redirect('/sso/login')->withErrors(['sso' => 'Invalid or expired SSO token']);
})->name('sso.login');

Route::middleware(['auth', 'sentinel-log.geofence', 'sentinel-log.2fa'])->group(function () {
    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');
    Route::post('/logout', fn() => Auth::logout() && redirect('/'))->name('logout');
    Route::match(['GET', 'POST'], '/sso/generate', function (SsoAuthenticationService $ssoService) {
        if (request()->isMethod('POST')) {
            $token = $ssoService->generateToken(Auth::user(), 'test_client');
            return view('sso_generate', compact('token'));
        }
        return view('sso_generate');
    })->name('sso.generate');
    Route::match(['GET', 'POST'], '/2fa/setup', function (TwoFactorAuthenticationService $service) {
        if (request()->isMethod('POST')) {
            if (request('code')) {
                $user = Auth::user();
                if ($service->verifyCode(request('secret'), request('code'), 1)) {
                    $user->update(['two_factor_secret' => request('secret'), 'two_factor_enabled_at' => now()]);
                    return redirect()->route('dashboard')->with('success', '2FA enabled successfully!');
                }
                return back()->withErrors(['code' => 'Invalid 2FA code']);
            }
            $secret = $service->generateSecret();
            $qrCodeUrl = $service->getQrCodeUrl($secret, Auth::user()->email);
            return view('2fa_setup', compact('secret', 'qrCodeUrl'));
        }
        return view('2fa_setup');
    })->name('2fa.setup');
    Route::delete('/2fa/disable', function () {
        Auth::user()->update(['two_factor_secret' => null, 'two_factor_enabled_at' => null]);
        return redirect()->route('dashboard')->with('success', '2FA disabled successfully!');
    })->name('2fa.disable');
});
