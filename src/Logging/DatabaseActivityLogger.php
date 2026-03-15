<?php

declare(strict_types=1);

namespace Antroly\Foundation\Logging;

use App\Logging\Contracts\ActivityLogger;
use Illuminate\Support\Facades\Auth;

class DatabaseActivityLogger implements ActivityLogger
{
    public function info(string $message, array $context = []): void
    {
        $this->write('info', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->write('warning', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->write('error', $message, $context);
    }

    private function write(string $level, string $message, array $context): void
    {
        try {
            ActivityLog::create([
                'level'   => $level,
                'message' => $message,
                'context' => empty($context) ? null : $context,
                'url'     => request()?->fullUrl(),
                'ip'      => request()?->ip(),
                'user_id' => Auth::id(),
            ]);
        } catch (\Throwable) {
            // Silently fail — logger must never throw
        }
    }
}
