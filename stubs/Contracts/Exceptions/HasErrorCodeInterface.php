<?php

declare(strict_types=1);

namespace App\Contracts\Exceptions;

interface HasErrorCodeInterface
{
    public function getStatusCode(): int;

    public function getErrorCode(): string;
}
