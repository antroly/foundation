<?php

declare(strict_types=1);

namespace Antroly\Foundation;

use Antroly\Foundation\Console\InstallCommand;
use Antroly\Foundation\Console\MakeActionCommand;
use Illuminate\Support\ServiceProvider;

class FoundationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerPublishes();

        if ($this->app->runningInConsole()) {
            $this->registerCommands();
        }
    }

    private function registerPublishes(): void
    {
        $this->publishes([
            // Actions
            __DIR__ . '/../stubs/Actions/Action.php'                             => app_path('Actions/Action.php'),
            // Contracts - DTO boundaries
            __DIR__ . '/../stubs/Contracts/Dto/BuildsFromRequest.php'            => app_path('Contracts/Dto/BuildsFromRequest.php'),
            __DIR__ . '/../stubs/Contracts/Dto/ResultData.php'                   => app_path('Contracts/Dto/ResultData.php'),
            // Contracts - exceptions
            __DIR__ . '/../stubs/Contracts/Exceptions/HasErrorCodeInterface.php' => app_path('Contracts/Exceptions/HasErrorCodeInterface.php'),
            // Exceptions
            __DIR__ . '/../stubs/Exceptions/DomainException.php'                 => app_path('Exceptions/DomainException.php'),
            __DIR__ . '/../stubs/Exceptions/AppExceptionHandler.php'             => app_path('Exceptions/AppExceptionHandler.php'),
            // HTTP
            __DIR__ . '/../stubs/Http/Controllers/BaseController.php'            => app_path('Http/Controllers/BaseController.php'),
            __DIR__ . '/../stubs/Http/Resources/BaseResource.php'                => app_path('Http/Resources/BaseResource.php'),
            __DIR__ . '/../stubs/Http/ViewModels/BaseViewModel.php'              => app_path('Http/ViewModels/BaseViewModel.php'),
            // Logging
            __DIR__ . '/../stubs/Logging/Contracts/AppLogger.php'               => app_path('Logging/Contracts/AppLogger.php'),
            __DIR__ . '/../stubs/Logging/DatabaseLogger.php'                    => app_path('Logging/DatabaseLogger.php'),
            // Models
            __DIR__ . '/../stubs/Models/ActivityLog.php'                        => app_path('Models/ActivityLog.php'),
            // HTTP macros
            __DIR__ . '/../stubs/Http/Macros/ResponseMacros.php'                => app_path('Http/Macros/ResponseMacros.php'),
            // Providers
            __DIR__ . '/../stubs/Providers/AppServiceProvider.php'              => app_path('Providers/AppServiceProvider.php'),
        ], 'antroly-foundation');

        $this->publishes([
            __DIR__ . '/../database/migrations/create_logs_table.php' => database_path('migrations/' . date('Y_m_d_His') . '_create_logs_table.php'),
        ], 'antroly-migrations');

        $this->publishes([
            __DIR__ . '/../stubs/Tests/ArchitectureTest.php' => base_path('tests/Architecture/ArchitectureTest.php'),
        ], 'antroly-tests');
    }

    private function registerCommands(): void
    {
        $this->commands([
            InstallCommand::class,
            MakeActionCommand::class,
        ]);
    }
}
