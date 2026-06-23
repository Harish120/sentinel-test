@extends('layouts.app')

@section('title', 'Changelog')

@section('content')

{{-- Header --}}
<div class="card p-4 mb-4">
    <div class="d-flex align-items-center justify-content-between mb-1">
        <h2 class="mb-0">harryes/laravel-sentinellog</h2>
        <span class="badge bg-primary fs-6">v0.2.1</span>
    </div>
    <p class="text-muted mb-0">Upgrade notes across all releases</p>
</div>

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- v0.2.1                                                                --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
<div class="card p-4 mb-3 border-primary">
    <h5 class="fw-bold mb-3">
        <span class="badge bg-primary me-2">v0.2.1</span>
        Bug-fix release
    </h5>

    <div class="mb-3">
        <h6 class="fw-semibold">
            <span class="badge bg-danger me-1">FIX</span>
            Middleware aliases always registered
        </h6>
        <p class="mb-1 text-muted" style="font-size:.9rem;">
            In v0.2.0 the <code>sentinel-log.2fa</code> and <code>sentinel-log.geofence</code>
            aliases were only registered when the corresponding feature was enabled in config.
            Any app that added these aliases to a route group got a fatal
            <code>Target class [sentinel-log.geofence] does not exist</code> 500 error on
            every request when the feature was disabled (the default).
        </p>
        <p class="mb-0 text-muted" style="font-size:.9rem;">
            Both middleware classes already self-guard at runtime — <code>EnforceGeoFencing</code>
            checks <code>geo_fencing.enabled</code> itself and passes through when off;
            <code>EnforceTwoFactorAuthentication</code> passes through when the user does not
            implement <code>TwoFactorAuthenticatable</code>. The aliases are now registered
            unconditionally so apps can add them to routes freely regardless of feature state.
        </p>
    </div>

    <div>
        <h6 class="fw-semibold">
            <span class="badge bg-danger me-1">FIX</span>
            View publish path corrected
        </h6>
        <p class="mb-0 text-muted" style="font-size:.9rem;">
            Published views now land at <code>resources/views/sentinel-log/</code> instead of
            the confusing <code>resources/views/vendor/sentinel-log/</code> path used in v0.2.0.
            Publishing is still optional — the package serves the confirmation pages directly
            via <code>loadViewsFrom</code> with no publishing step required.
        </p>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- v0.2.0                                                                --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
<div class="card p-4 mb-3">
    <h5 class="fw-bold mb-3">
        <span class="badge bg-secondary me-2">v0.2.0</span>
        What changed from v0.1.0
    </h5>

    {{-- 1. Location Verification --}}
    <div class="mb-4">
        <h6 class="fw-semibold">
            <span class="badge bg-success me-1">NEW</span>
            Location Verification with Confirmation Pages
        </h6>
        <p class="mb-2 text-muted" style="font-size:.9rem;">
            When a login is detected from a new city/country the package sends an email with
            <strong>verify</strong> and <strong>deny</strong> links. In v0.1 those links acted
            immediately — a problem for email-scanning bots that prefetch every URL.
            In v0.2 the GET endpoints show a human-readable confirmation page; only the
            subsequent POST actually performs the action.
        </p>
        <ul class="mb-1" style="font-size:.85rem;">
            <li><code>GET  /sentinel-log/location/verify/{token}</code> — confirmation page</li>
            <li><code>POST /sentinel-log/location/verify/{token}</code> — marks the session trusted</li>
            <li><code>GET  /sentinel-log/location/deny/{token}</code>   — confirmation page</li>
            <li><code>POST /sentinel-log/location/deny/{token}</code>   — revokes the session</li>
        </ul>
        <p class="mb-0 text-muted" style="font-size:.85rem;">
            Routes and Blade views are served automatically by the package — no publishing needed.
            To customise: <code>php artisan vendor:publish --tag=sentinel-log-views</code>
        </p>
    </div>

    {{-- 2. 2FA Session Enforcement --}}
    <div class="mb-4">
        <h6 class="fw-semibold">
            <span class="badge bg-warning text-dark me-1">IMPROVED</span>
            Two-Factor Session Enforcement
        </h6>
        <p class="mb-2 text-muted" style="font-size:.9rem;">
            The <code>sentinel-log.2fa</code> middleware now enforces two separate checks:
        </p>
        <ol style="font-size:.85rem;" class="mb-2">
            <li><strong>Not set up →</strong> redirects to <code>two-factor.setup</code> (unchanged)</li>
            <li><strong>Set up but session has no <code>2fa_verified</code> key →</strong>
                redirects to <code>two-factor.verify</code> (NEW). Every fresh login is now challenged.</li>
        </ol>
        <pre class="bg-light p-2 rounded mb-0" style="font-size:.78rem;">'two_factor' => [
    'required'     => env('SENTINEL_LOG_2FA_REQUIRED', false),
    'setup_route'  => 'two-factor.setup',
    'verify_route' => 'two-factor.verify',
]</pre>
    </div>

    {{-- 3. Encrypted TOTP Secret --}}
    <div class="mb-4">
        <h6 class="fw-semibold">
            <span class="badge bg-warning text-dark me-1">IMPROVED</span>
            TOTP Secret Encrypted at Rest
        </h6>
        <p class="mb-0 text-muted" style="font-size:.9rem;">
            Add <code>'two_factor_secret' => 'encrypted'</code> to your User model's
            <code>$casts</code>. Laravel encrypts/decrypts the TOTP seed using
            <code>APP_KEY</code> — a database dump never exposes raw secrets.
        </p>
    </div>

    {{-- 4. Device Cookie --}}
    <div class="mb-4">
        <h6 class="fw-semibold">
            <span class="badge bg-warning text-dark me-1">IMPROVED</span>
            Persistent Device Token (HttpOnly Cookie)
        </h6>
        <p class="mb-1 text-muted" style="font-size:.9rem;">
            Device recognition in v0.1 relied on a header hash that included the IP address,
            causing false "new device" alerts for mobile users on changing IPs.
        </p>
        <p class="mb-0 text-muted" style="font-size:.9rem;">
            v0.2 issues a cryptographically random <code>sentinel_device_token</code> cookie
            (HttpOnly, SameSite=Lax, 2-year lifetime) on first login. The token is stored as
            <code>device_info.token</code> in every auth log row — stable across IP and UA changes.
            The header hash is still recorded as a secondary forensic signal.
        </p>
    </div>

    {{-- 5. Geo Provider --}}
    <div class="mb-4">
        <h6 class="fw-semibold">
            <span class="badge bg-warning text-dark me-1">IMPROVED</span>
            Geolocation Provider → ipwho.is (HTTPS)
        </h6>
        <p class="mb-0 text-muted" style="font-size:.9rem;">
            Default provider changed from <code>http://ip-api.com</code> to
            <code>https://ipwho.is</code> — free, no API key, HTTPS-only. Configurable via
            <code>geo_provider_url</code> / <code>SENTINEL_LOG_GEO_PROVIDER_URL</code>.
            Custom providers must return JSON with:
            <code>success</code>, <code>country</code>, <code>city</code>,
            <code>latitude</code>, <code>longitude</code>, <code>ip</code>.
        </p>
    </div>

    {{-- 6. Scheduler --}}
    <div class="mb-4">
        <h6 class="fw-semibold">
            <span class="badge bg-success me-1">NEW</span>
            Scheduler Pruning Hooks
        </h6>
        <p class="mb-2 text-muted" style="font-size:.9rem;">
            Three new prune methods keep sentinel tables lean. Add to your scheduler:
        </p>
        <pre class="bg-light p-2 rounded mb-0" style="font-size:.78rem;">Schedule::call(fn () => AuthenticationLog::pruneOlderThan())->daily();
Schedule::call(fn () => app(BruteForceProtectionService::class)->pruneExpired())->daily();
Schedule::call(fn () => app(LocationVerificationService::class)->pruneExpired())->daily();</pre>
    </div>

    {{-- 7. Migration --}}
    <div>
        <h6 class="fw-semibold">
            <span class="badge bg-success me-1">NEW</span>
            location_verifications Migration
        </h6>
        <p class="mb-0 text-muted" style="font-size:.9rem;">
            A new <code>location_verifications</code> table stores pending verify/deny tokens
            with expiry, and records <code>verified_at</code> / <code>denied_at</code> once
            actioned. Run <code>php artisan migrate</code> after upgrading.
        </p>
    </div>
</div>

{{-- Back links --}}
<div class="text-center mt-2 mb-4">
    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">← Back to Dashboard</a>
    <a href="https://github.com/Harish120/laravel-sentinellog" target="_blank" class="btn btn-outline-dark btn-sm ms-2">GitHub</a>
</div>

@endsection
