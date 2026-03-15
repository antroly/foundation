<?php

declare(strict_types=1);

namespace App\Providers;

use App\Http\Macros\ResponseMacros;
use App\Logging\Contracts\ActivityLoggerInterface;
use App\Logging\ActivityLogger;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ActivityLoggerInterface::class, ActivityLogger::class);
    }

    public function boot(): void
    {
        ResponseMacros::register();
    }
}
