<?php

declare(strict_types=1);

namespace App\Providers;

use App\Http\Macros\ResponseMacros;
use App\Logging\Contracts\AppLogger;
use App\Logging\DatabaseLogger;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AppLogger::class, DatabaseLogger::class);
    }

    public function boot(): void
    {
        ResponseMacros::register();
    }
}
