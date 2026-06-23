<?php

use Harryes\SentinelLog\Models\AuthenticationLog;
use Harryes\SentinelLog\Services\BruteForceProtectionService;
use Harryes\SentinelLog\Services\LocationVerificationService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Sentinel Log — Daily Pruning
|--------------------------------------------------------------------------
| Keep sentinel tables lean by removing records that no longer need to be
| retained. All three jobs are fast DELETE queries — safe to run daily.
|--------------------------------------------------------------------------
*/

Schedule::call(fn () => AuthenticationLog::pruneOlderThan())->daily()
    ->name('sentinel:prune-auth-logs')
    ->withoutOverlapping();

Schedule::call(fn () => app(BruteForceProtectionService::class)->pruneExpired())->daily()
    ->name('sentinel:prune-blocked-ips')
    ->withoutOverlapping();

Schedule::call(fn () => app(LocationVerificationService::class)->pruneExpired())->daily()
    ->name('sentinel:prune-location-verifications')
    ->withoutOverlapping();