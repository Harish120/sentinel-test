<?php

namespace App\Providers;

use Harryes\SentinelLog\Middleware\EnforceGeoFencing;
use Harryes\SentinelLog\Middleware\EnforceTwoFactorAuthentication;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        /*
         * Always register sentinel middleware aliases so routes that reference
         * them never throw BindingResolutionException when a feature is disabled.
         * Each middleware class self-guards via its own config check and passes
         * through immediately when the feature is off.
         */
        Route::aliasMiddleware('sentinel-log.2fa', EnforceTwoFactorAuthentication::class);
        Route::aliasMiddleware('sentinel-log.geofence', EnforceGeoFencing::class);
    }
}
