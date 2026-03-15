<?php

declare(strict_types=1);

namespace App\Logging\Contracts;

interface ActivityLogger
{
    public function info(string $message, array $context = []): void;

    public function warning(string $message, array $context = []): void;

    public function error(string $message, array $context = []): void;
}
