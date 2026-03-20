<?php

declare(strict_types=1);

namespace Antroly\Foundation\Logging;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class DatabaseLogger implements AppLogger
{
    /** @param array<string, mixed> $context */
    public function info(string $message, array $context = []): void
    {
        $this->write('info', $message, $context);
    }

    /** @param array<string, mixed> $context */
    public function warning(string $message, array $context = []): void
    {
        $this->write('warning', $message, $context);
    }

    /** @param array<string, mixed> $context */
    public function error(string $message, array $context = []): void
    {
        $this->write('error', $message, $context);
    }

    /** @param array<string, mixed> $context */
    private function write(string $level, string $message, array $context): void
    {
        try {
            ActivityLog::create([
                'level'   => $level,
                'message' => $message,
                'context' => empty($context) ? null : $context,
                'url'     => Request::fullUrl(),
                'ip'      => Request::ip(),
                'user_id' => Auth::id(),
            ]);
        } catch (\Throwable) {
            // Silently fail - logger must never throw
        }
    }
}
