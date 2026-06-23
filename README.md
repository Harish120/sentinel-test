# sentinel-test

Demo application for the [`harryes/laravel-sentinellog`](https://github.com/Harish120/laravel-sentinellog) package.
It exercises every major feature so you can see real behaviour rather than reading theory.

---

## What this demo covers

| Feature | Where to see it |
|---------|----------------|
| Login / logout event logging | Dashboard → Recent Auth Logs |
| Brute-force protection | Login page — attempt counter after a failed login |
| Device recognition (HttpOnly cookie) | Dashboard → Current Device panel |
| Location verification (verify / deny email links) | Triggered on first login from a new city |
| Two-factor authentication (TOTP) | Dashboard → Setup 2FA |
| SSO token generation & login | Dashboard → Generate SSO Token |
| Geo-fencing | Enable via `SENTINEL_LOG_GEO_FENCING_ENABLED=true` |
| Session hijacking detection | Automatic — logged under auth events |
| Daily pruning scheduler | `routes/console.php` |
| v0.1 → v0.2 upgrade notes | `/changelog` route |

---

## Requirements

- PHP ^8.2
- Composer
- SQLite (default — zero config) **or** MySQL / PostgreSQL

---

## Quick start

```bash
git clone https://github.com/Harish120/sentinel-test.git
cd sentinel-test

composer install

cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed          # creates demo user
php artisan serve
```

Default demo credentials (see `database/seeders/DatabaseSeeder.php`):

| Field    | Value             |
|----------|-------------------|
| Email    | test@example.com  |
| Password | password123       |

---

## Package version

```json
"harryes/laravel-sentinellog": "^0.2.1"
```

---

## Integration guide

### 1. User model

Your `User` model must use the `NotifiesAuthenticationEvents` trait and implement both
contracts so the middleware and listeners can work correctly:

```php
use Harryes\SentinelLog\Contracts\NotifiableWithFailedAttempt;
use Harryes\SentinelLog\Contracts\TwoFactorAuthenticatable;
use Harryes\SentinelLog\Traits\NotifiesAuthenticationEvents;

class User extends Authenticatable implements TwoFactorAuthenticatable, NotifiableWithFailedAttempt
{
    use NotifiesAuthenticationEvents, HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'two_factor_secret'     => 'encrypted',  // encrypts TOTP seed at rest
            'two_factor_enabled_at' => 'datetime',
            'password'              => 'hashed',
        ];
    }

    public function getTwoFactorSecret(): ?string
    {
        return $this->two_factor_secret;
    }

    public function getTwoFactorEnabledAt(): ?\DateTimeInterface
    {
        return $this->two_factor_enabled_at;
    }
}
```

### 2. Migrations

```bash
php artisan migrate
```

The package ships six migrations:

| Table | Purpose |
|-------|---------|
| `authentication_logs` | Every login / logout / failed event |
| `sentinel_sessions` | Active session tracking |
| `blocked_ips` | Brute-force blocks |
| `sso_tokens` | SSO one-time tokens |
| `location_verifications` | Pending verify / deny email tokens *(new in v0.2)* |
| Users table columns | `two_factor_secret`, `two_factor_enabled_at` |

### 3. Middleware

The package registers both middleware aliases unconditionally (fixed in v0.2.1).
Add them to any route group — they pass through safely when the feature is disabled:

```php
Route::middleware(['auth', 'sentinel-log.geofence', 'sentinel-log.2fa'])->group(function () {
    // protected routes
});
```

Your app must define named routes that the 2FA middleware redirects to:

```php
Route::get('/two-factor/verify', ...)->name('two-factor.verify');
Route::get('/two-factor/setup',  ...)->name('two-factor.setup');
```

### 4. Two-factor verify route

The `sentinel-log.2fa` middleware redirects to `two-factor.verify` when a user has 2FA
set up but hasn't completed the TOTP challenge this session. The route must set the
`2fa_verified` session key on success:

```php
Route::post('/two-factor/verify', function (TwoFactorAuthenticationService $twoFactor) {
    if ($twoFactor->verifyCode(Auth::user()->two_factor_secret, request('code'))) {
        session(['2fa_verified' => true]);
        return redirect()->route('dashboard');
    }
    return back()->withErrors(['code' => 'Invalid 2FA code']);
})->middleware('auth');
```

### 5. Scheduler — daily pruning

Add to `routes/console.php` (or your `Console/Kernel.php`):

```php
use Harryes\SentinelLog\Models\AuthenticationLog;
use Harryes\SentinelLog\Services\BruteForceProtectionService;
use Harryes\SentinelLog\Services\LocationVerificationService;

Schedule::call(fn () => AuthenticationLog::pruneOlderThan())->daily();
Schedule::call(fn () => app(BruteForceProtectionService::class)->pruneExpired())->daily();
Schedule::call(fn () => app(LocationVerificationService::class)->pruneExpired())->daily();
```

### 6. Key config options (`config/sentinel-log.php`)

```php
'two_factor' => [
    'enabled'      => env('SENTINEL_LOG_2FA_ENABLED', false),
    'required'     => env('SENTINEL_LOG_2FA_REQUIRED', false), // force all users to set up 2FA
    'setup_route'  => 'two-factor.setup',
    'verify_route' => 'two-factor.verify',
],

'geo_provider_url' => env('SENTINEL_LOG_GEO_PROVIDER_URL', 'https://ipwho.is'),

'location_verification' => [
    'enabled'   => env('SENTINEL_LOG_LOCATION_VERIFICATION_ENABLED', true),
    'token_ttl' => 30, // minutes until verify/deny links expire
],
```

### 7. Customising location verification views (optional)

The package serves its own Blade views for the verify/deny confirmation pages — no
publishing required. If you want to customise them:

```bash
php artisan vendor:publish --tag=sentinel-log-views
```

Views are published to `resources/views/sentinel-log/location/`.

---

## Environment variables reference

| Variable | Default | Purpose |
|----------|---------|---------|
| `SENTINEL_LOG_ENABLED` | `true` | Master on/off switch |
| `SENTINEL_LOG_2FA_ENABLED` | `false` | Enable 2FA middleware registration |
| `SENTINEL_LOG_2FA_REQUIRED` | `false` | Force all users to configure 2FA |
| `SENTINEL_LOG_NOTIFY_NEW_DEVICE` | `true` | Email on new device login |
| `SENTINEL_LOG_NOTIFY_FAILED_ATTEMPT` | `true` | Email after repeated failures |
| `SENTINEL_LOG_BRUTE_FORCE_ENABLED` | `true` | Block IPs after threshold failures |
| `SENTINEL_LOG_GEO_FENCING_ENABLED` | `false` | Block logins outside allowed countries |
| `SENTINEL_LOG_GEO_FENCING_ALLOWED_COUNTRIES` | `United States,Canada` | Comma-separated list |
| `SENTINEL_LOG_GEO_PROVIDER_URL` | `https://ipwho.is` | Geolocation API base URL |
| `SENTINEL_LOG_LOCATION_VERIFICATION_ENABLED` | `true` | Send verify/deny emails on new location |
| `SENTINEL_LOG_SSO_ENABLED` | `false` | Enable SSO token flow |
| `SENTINEL_LOG_SESSIONS_ENABLED` | `true` | Track and limit active sessions |

---

## License

MIT
