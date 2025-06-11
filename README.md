# Laravel Sentinel Logs

A Laravel application demonstrating advanced authentication logging and security features using the `harryes/laravel-sentinellog` package.

## Features

- Authentication event logging (login, logout, failed attempts)
- Device tracking and management
- Geolocation tracking for login attempts
- Suspicious activity notifications
- Two-Factor Authentication (2FA) support
- Session management and monitoring
- Brute force protection
- Geo-fencing capabilities

## Requirements

- PHP ^8.2
- Laravel ^12.0
- Composer
- MySQL/PostgreSQL database

## Installation

1. Clone the repository:
```bash
git clone <your-repository-url>
cd sentinel-test
```

2. Install dependencies:
```bash
composer install
```

3. Create and configure your environment file:
```bash
cp .env.example .env
```

4. Generate application key:
```bash
php artisan key:generate
```

5. Configure your database in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

6. Run migrations:
```bash
php artisan migrate
```

7. Configure Sentinel Log in your `.env`:
```env
SENTINEL_LOG_ENABLED=true
SENTINEL_LOG_NOTIFICATIONS_ENABLED=true
SENTINEL_LOG_2FA_ENABLED=true
```

## Configuration

### Basic Configuration

The sentinel log configuration can be published and modified:

```bash
php artisan vendor:publish --tag=sentinel-log-config
```

This will create a `config/sentinel-log.php` file where you can customize:

- Logging settings
- Notification preferences
- Security thresholds
- Geo-fencing rules
- Device tracking options

### Two-Factor Authentication

To enable 2FA support:

1. Ensure your User model implements the necessary trait:

```php
use Harryes\SentinelLog\Traits\HasTwoFactorAuth;

class User extends Authenticatable
{
    use HasTwoFactorAuth;
    // ...
}
```

2. Add the 2FA middleware to routes requiring additional security:

```php
Route::middleware(['auth', 'sentinel.2fa'])->group(function () {
    // Protected routes
});
```

### Notifications

The package sends notifications for:
- New device logins
- Suspicious login attempts
- Failed authentication attempts
- Geolocation changes

Configure notification channels in `config/sentinel-log.php`:

```php
'notifications' => [
    'channels' => ['mail', 'database'],
    'notify_on_new_device' => true,
    'notify_on_suspicious_activity' => true,
]
```

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

For support, please email support@yourdomain.com or create an issue in the repository.
