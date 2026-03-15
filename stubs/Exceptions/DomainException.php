<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Contracts\Exceptions\HasErrorCodeInterface;
use Exception;

abstract class DomainException extends Exception implements HasErrorCodeInterface
{
    protected int $statusCode;

    protected string $errorCode;

    public function __construct(string $message, int $statusCode, string $errorCode)
    {
        parent::__construct($message);
        $this->statusCode = $statusCode;
        $this->errorCode = $errorCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
}
