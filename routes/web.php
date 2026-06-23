<?php

use App\Models\User;
use Harryes\SentinelLog\Services\SsoAuthenticationService;
use Harryes\SentinelLog\Services\TwoFactorAuthenticationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => view('welcome'));
Route::get('/changelog', fn () => view('changelog'))->name('changelog');

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::get('/login', fn () => view('login'))->name('login');

Route::post('/login', function () {
    if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
        return redirect()->route('dashboard');
    }

    return back()->withErrors(['email' => 'Invalid credentials']);
});

/*
|--------------------------------------------------------------------------
| Two-Factor Authentication
| Routes must be outside the auth middleware so unauthenticated users who
| arrive via email link can still access the verify page, and so the
| sentinel-log.2fa middleware does not loop when redirecting here.
|--------------------------------------------------------------------------
*/

Route::get('/two-factor/verify', fn () => view('2fa_verify'))->name('two-factor.verify');

Route::post('/two-factor/verify', function (TwoFactorAuthenticationService $twoFactor) {
    $user = Auth::user();

    if ($twoFactor->verifyCode($user->two_factor_secret, request('code'))) {
        session(['2fa_verified' => true]);

        return redirect()->route('dashboard');
    }

    return back()->withErrors(['code' => 'Invalid 2FA code']);
})->middleware('auth');

/*
|--------------------------------------------------------------------------
| SSO
|--------------------------------------------------------------------------
*/

Route::match(['GET', 'POST'], '/sso/login', function (SsoAuthenticationService $sso) {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    $token = request('sso_token');

    if (! $token) {
        return view('sso_login');
    }

    $user = $sso->validateToken($token, config('sentinel-log.sso.client_id', 'test_client'));

    if ($user) {
        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Logged in via SSO successfully!');
    }

    return redirect('/sso/login')->withErrors(['sso' => 'Invalid or expired SSO token']);
})->name('sso.login');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'sentinel-log.geofence', 'sentinel-log.2fa'])->group(function () {

    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');

    Route::post('/logout', fn () => Auth::logout() && redirect('/'))->name('logout');

    /*
    | SSO Token Generation
    */
    Route::match(['GET', 'POST'], '/sso/generate', function (SsoAuthenticationService $sso) {
        if (request()->isMethod('POST')) {
            $token = $sso->generateToken(Auth::user(), 'test_client');

            return view('sso_generate', compact('token'));
        }

        return view('sso_generate');
    })->name('sso.generate');

    /*
    | 2FA Setup & Management
    */
    Route::get('/two-factor/setup', function (TwoFactorAuthenticationService $twoFactor) {
        return view('2fa_setup');
    })->name('two-factor.setup');

    Route::post('/two-factor/setup', function (TwoFactorAuthenticationService $twoFactor) {
        if (request('code')) {
            $user = Auth::user();

            if ($twoFactor->verifyCode(request('secret'), request('code'), 1)) {
                $user->update([
                    'two_factor_secret'     => request('secret'),
                    'two_factor_enabled_at' => now(),
                ]);

                return redirect()->route('dashboard')->with('success', '2FA enabled successfully!');
            }

            return back()->withErrors(['code' => 'Invalid 2FA code']);
        }

        $secret    = $twoFactor->generateSecret();
        $qrCodeUrl = $twoFactor->getQrCodeUrl($secret, Auth::user()->email);

        return view('2fa_setup', compact('secret', 'qrCodeUrl'));
    });

    Route::delete('/two-factor/disable', function () {
        Auth::user()->update([
            'two_factor_secret'     => null,
            'two_factor_enabled_at' => null,
        ]);

        session()->forget('2fa_verified');

        return redirect()->route('dashboard')->with('success', '2FA disabled successfully!');
    })->name('2fa.disable');
});
