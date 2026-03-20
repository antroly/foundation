<?php

declare(strict_types=1);

namespace Antroly\Foundation\Logging\Contracts;

/**
 * Package-owned logging contract.
 *
 * Apps that publish the Antroly stubs will receive their own copy of this
 * interface under App\Logging\Contracts\AppLogger and should rebind it
 * in their AppServiceProvider. Until then, DatabaseLogger is used directly.
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
