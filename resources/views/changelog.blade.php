@extends('layouts.app')

@section('title', 'Changelog — v0.1 → v0.2')

@section('content')

<div class="card p-4 mb-4">
    <div class="d-flex align-items-center justify-content-between mb-1">
        <h2 class="mb-0">harryes/laravel-sentinellog</h2>
        <span class="badge bg-primary fs-6">v0.2.0</span>
    </div>
    <p class="text-muted mb-0">Upgrade notes — what changed from v0.1.0 to v0.2.0</p>
</div>

{{-- ── 1. Location Verification ─────────────────────────────────────────── --}}
<div class="card p-4 mb-3">
    <h5 class="fw-bold mb-1">
        <span class="badge bg-success me-2">NEW</span>
        Location Verification with Confirmation Pages
    </h5>
    <p class="mb-2 text-muted" style="font-size:.9rem;">
        When a login is detected from a new city/country the package sends an email with
        <strong>verify</strong> and <strong>deny</strong> links. In v0.1 those links acted
        immediately — a problem for email-scanning bots that prefetch every URL.
    </p>
    <p class="mb-2 text-muted" style="font-size:.9rem;">
        In v0.2 the GET endpoints show a human-readable confirmation page; only the
        subsequent POST actually performs the action.
    </p>
    <ul class="mb-2" style="font-size:.85rem;">
        <li><code>GET  /sentinel-log/location/verify/{token}</code> — confirmation page</li>
        <li><code>POST /sentinel-log/location/verify/{token}</code> — marks the session trusted</li>
        <li><code>GET  /sentinel-log/location/deny/{token}</code>   — confirmation page</li>
        <li><code>POST /sentinel-log/location/deny/{token}</code>   — revokes the session</li>
    </ul>
    <p class="mb-0" style="font-size:.85rem;">
        The routes and Blade views are registered by the package automatically.
        Publish customisable copies with:
        <code>php artisan vendor:publish --tag=sentinel-log-views</code>
    </p>
</div>

{{-- ── 2. 2FA Session Enforcement ───────────────────────────────────────── --}}
<div class="card p-4 mb-3">
    <h5 class="fw-bold mb-1">
        <span class="badge bg-warning text-dark me-2">IMPROVED</span>
        Two-Factor Session Enforcement
    </h5>
    <p class="mb-2 text-muted" style="font-size:.9rem;">
        The <code>sentinel-log.2fa</code> middleware now enforces <em>two</em> separate checks:
    </p>
    <ol style="font-size:.85rem;" class="mb-2">
        <li>
            <strong>Not set up →</strong> redirects to <code>two-factor.setup</code>
            (unchanged from v0.1).
        </li>
        <li>
            <strong>Set up but not yet verified this session →</strong> redirects to
            <code>two-factor.verify</code> (NEW). This ensures every fresh login is challenged
            with a TOTP code, not just the first time 2FA is enabled.
        </li>
    </ol>
    <p class="mb-2" style="font-size:.85rem;">
        Two new config keys control the route names the middleware redirects to:
    </p>
    <pre class="bg-light p-2 rounded mb-0" style="font-size:.78rem;">'two_factor' => [
    'required'     => env('SENTINEL_LOG_2FA_REQUIRED', false),
    'setup_route'  => 'two-factor.setup',
    'verify_route' => 'two-factor.verify',
]</pre>
</div>

{{-- ── 3. Encrypted TOTP Secret ─────────────────────────────────────────── --}}
<div class="card p-4 mb-3">
    <h5 class="fw-bold mb-1">
        <span class="badge bg-warning text-dark me-2">IMPROVED</span>
        TOTP Secret Encrypted at Rest
    </h5>
    <p class="mb-0 text-muted" style="font-size:.9rem;">
        Add <code>'two_factor_secret' => 'encrypted'</code> to your User model's
        <code>$casts</code>. Laravel will transparently encrypt/decrypt the TOTP seed
        using <code>APP_KEY</code>, so a database dump never exposes raw secrets.
    </p>
</div>

{{-- ── 4. Device Cookie ─────────────────────────────────────────────────── --}}
<div class="card p-4 mb-3">
    <h5 class="fw-bold mb-1">
        <span class="badge bg-warning text-dark me-2">IMPROVED</span>
        Persistent Device Token (HttpOnly Cookie)
    </h5>
    <p class="mb-2 text-muted" style="font-size:.9rem;">
        Device recognition in v0.1 relied on a header hash derived from IP + User-Agent,
        which caused false "new device" alerts for mobile users on changing IPs.
    </p>
    <p class="mb-0 text-muted" style="font-size:.9rem;">
        v0.2 issues a cryptographically random <code>sentinel_device_token</code>
        cookie (HttpOnly, SameSite=Lax, 2-year lifetime) on first login. This token is
        stored as <code>device_info.token</code> in every authentication log row and
        becomes the primary device identity signal — stable across IP and UA changes.
        The secondary header hash is still recorded for forensic use.
    </p>
</div>

{{-- ── 5. Geo Provider ──────────────────────────────────────────────────── --}}
<div class="card p-4 mb-3">
    <h5 class="fw-bold mb-1">
        <span class="badge bg-warning text-dark me-2">IMPROVED</span>
        Geolocation Provider → ipwho.is (HTTPS)
    </h5>
    <p class="mb-0 text-muted" style="font-size:.9rem;">
        The default provider changed from <code>http://ip-api.com</code> to
        <code>https://ipwho.is</code> — free, no API key, and HTTPS-only. The URL is
        configurable via <code>geo_provider_url</code> config key or the
        <code>SENTINEL_LOG_GEO_PROVIDER_URL</code> env variable. Custom providers must
        return JSON with: <code>success</code>, <code>country</code>, <code>city</code>,
        <code>latitude</code>, <code>longitude</code>, <code>ip</code>.
    </p>
</div>

{{-- ── 6. Scheduler / Pruning ───────────────────────────────────────────── --}}
<div class="card p-4 mb-3">
    <h5 class="fw-bold mb-1">
        <span class="badge bg-success me-2">NEW</span>
        Scheduler Pruning Hooks
    </h5>
    <p class="mb-2 text-muted" style="font-size:.9rem;">
        Three new prune methods keep sentinel tables lean. Wire them into your scheduler:
    </p>
    <pre class="bg-light p-2 rounded mb-0" style="font-size:.78rem;">Schedule::call(fn () => AuthenticationLog::pruneOlderThan())->daily();
Schedule::call(fn () => app(BruteForceProtectionService::class)->pruneExpired())->daily();
Schedule::call(fn () => app(LocationVerificationService::class)->pruneExpired())->daily();</pre>
</div>

{{-- ── 7. Migration ─────────────────────────────────────────────────────── --}}
<div class="card p-4 mb-3">
    <h5 class="fw-bold mb-1">
        <span class="badge bg-success me-2">NEW</span>
        location_verifications Migration
    </h5>
    <p class="mb-0 text-muted" style="font-size:.9rem;">
        A new <code>location_verifications</code> table stores pending verify/deny tokens with
        expiry, and records <code>verified_at</code> / <code>denied_at</code> timestamps once
        actioned. Run <code>php artisan migrate</code> after upgrading.
    </p>
</div>

<div class="text-center mt-2 mb-4">
    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">← Back to Dashboard</a>
    <a href="https://github.com/Harish120/laravel-sentinellog" target="_blank" class="btn btn-outline-dark btn-sm ms-2">GitHub</a>
</div>

@endsection
