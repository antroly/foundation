<?php

declare(strict_types=1);

namespace App\Logging\Contracts;

/**
 * App-owned logging contract.
 *
 * Bound to DatabaseLogger in AppServiceProvider out of the box.
 * Swap the implementation by rebinding in AppServiceProvider:
 *
 *   $this->app->singleton(AppLogger::class, YourCustomLogger::class);
 */
interface AppLogger
{
    /** @param array<string, mixed> $context */
    public function info(string $message, array $context = []): void;

    /** @param array<string, mixed> $context */
    public function warning(string $message, array $context = []): void;

    /** @param array<string, mixed> $context */
    public function error(string $message, array $context = []): void;
}
