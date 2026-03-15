#!/usr/bin/env bash
set -e

# Usage: ./sync.sh /path/to/your/antroly-foundation
# Example: ./sync.sh ~/Projects/antroly/foundation

TARGET="${1:-.}"

if [ ! -d "$TARGET" ]; then
  mkdir -p "$TARGET"
fi

cd "$TARGET"
echo "Syncing antroly/foundation into: $(pwd)"
echo ""

mkdir -p "$(dirname "./composer.json")"
cat > "./composer.json" << 'END_OF_FILE_CONTENT_XQ9Z'
{
    "name": "antroly/foundation",
    "description": "Core package powering the Antroly architecture system for Laravel.",
    "type": "library",
    "version": "0.1.0",
    "license": "MIT",
    "keywords": ["laravel", "architecture", "actions", "dto", "antroly"],
    "authors": [
        {
            "name": "Antroly",
            "email": "hello@antroly.dev"
        }
    ],
    "require": {
        "php": "^8.2",
        "illuminate/support": "^11.0|^12.0",
        "illuminate/http": "^11.0|^12.0"
    },
    "require-dev": {
        "orchestra/testbench": "^9.0|^10.0",
        "pestphp/pest": "^3.0",
        "laravel/pint": "^1.0",
        "phpstan/phpstan": "^1.0"
    },
    "autoload": {
        "psr-4": {
            "Antroly\\Foundation\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Antroly\\Foundation\\Tests\\": "tests/"
        }
    },
    "extra": {
        "laravel": {
            "providers": [
                "Antroly\\Foundation\\FoundationServiceProvider"
            ]
        }
    },
    "config": {
        "sort-packages": true,
        "allow-plugins": {
            "pestphp/pest-plugin": true
        }
    },
    "minimum-stability": "stable",
    "prefer-stable": true
}
END_OF_FILE_CONTENT_XQ9Z
echo "  ✓ composer.json"

mkdir -p "$(dirname "./phpstan.neon")"
cat > "./phpstan.neon" << 'END_OF_FILE_CONTENT_XQ9Z'
parameters:
    level: 8
    paths:
        - src
        - stubs
END_OF_FILE_CONTENT_XQ9Z
echo "  ✓ phpstan.neon"

mkdir -p "$(dirname "./pint.json")"
cat > "./pint.json" << 'END_OF_FILE_CONTENT_XQ9Z'
{
    "preset": "laravel",
    "rules": {
        "declare_strict_types": true
    }
}
END_OF_FILE_CONTENT_XQ9Z
echo "  ✓ pint.json"

mkdir -p "$(dirname "./src/FoundationServiceProvider.php")"
cat > "./src/FoundationServiceProvider.php" << 'END_OF_FILE_CONTENT_XQ9Z'
<?php

declare(strict_types=1);

namespace Antroly\Foundation;

use Illuminate\Support\ServiceProvider;

class FoundationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->registerPublishes();
            $this->registerCommands();
        }
    }

    private function registerPublishes(): void
    {
        $this->publishes([
            // Actions
            __DIR__ . '/../stubs/Actions/Action.php'                                  => app_path('Actions/Action.php'),
            // DTOs
            __DIR__ . '/../stubs/Dtos/BaseDto.php'                                    => app_path('Dtos/BaseDto.php'),
            // Contracts
            __DIR__ . '/../stubs/Contracts/Exceptions/HasErrorCodeInterface.php'      => app_path('Contracts/Exceptions/HasErrorCodeInterface.php'),
            // Exceptions
            __DIR__ . '/../stubs/Exceptions/DomainException.php'                      => app_path('Exceptions/DomainException.php'),
            __DIR__ . '/../stubs/Exceptions/AppExceptionHandler.php'                  => app_path('Exceptions/AppExceptionHandler.php'),
            // HTTP
            __DIR__ . '/../stubs/Http/Controllers/BaseController.php'                 => app_path('Http/Controllers/BaseController.php'),
            __DIR__ . '/../stubs/Http/Macros/ResponseMacros.php'                      => app_path('Http/Macros/ResponseMacros.php'),
            __DIR__ . '/../stubs/Http/Resources/BaseResource.php'                     => app_path('Http/Resources/BaseResource.php'),
            __DIR__ . '/../stubs/Http/ViewModels/BaseViewModel.php'                   => app_path('Http/ViewModels/BaseViewModel.php'),
            // Logging
            __DIR__ . '/../stubs/Logging/ActivityLogger.php'                          => app_path('Logging/ActivityLogger.php'),
            __DIR__ . '/../stubs/Logging/Contracts/ActivityLoggerInterface.php'        => app_path('Logging/Contracts/ActivityLoggerInterface.php'),
            // Models
            __DIR__ . '/../stubs/Models/ActivityLog.php'                              => app_path('Models/ActivityLog.php'),
            // Providers
            __DIR__ . '/../stubs/Providers/AppServiceProvider.php'                    => app_path('Providers/AppServiceProvider.php'),
        ], 'antroly-foundation');

        $this->publishes([
            __DIR__ . '/../database/migrations/create_logs_table.php' => database_path('migrations/' . date('Y_m_d_His') . '_create_logs_table.php'),
        ], 'antroly-migrations');
    }

    private function registerCommands(): void
    {
        // Scaffolding commands registered here in a later step
    }
}
END_OF_FILE_CONTENT_XQ9Z
echo "  ✓ src/FoundationServiceProvider.php"

mkdir -p "$(dirname "./stubs/Actions/Action.php")"
cat > "./stubs/Actions/Action.php" << 'END_OF_FILE_CONTENT_XQ9Z'
<?php

declare(strict_types=1);

namespace App\Actions;

abstract class Action
{
    public static function run(mixed ...$arguments): mixed
    {
        return app(static::class)->execute(...$arguments);
    }
}
END_OF_FILE_CONTENT_XQ9Z
echo "  ✓ stubs/Actions/Action.php"

mkdir -p "$(dirname "./stubs/Contracts/Exceptions/HasErrorCodeInterface.php")"
cat > "./stubs/Contracts/Exceptions/HasErrorCodeInterface.php" << 'END_OF_FILE_CONTENT_XQ9Z'
<?php

declare(strict_types=1);

namespace App\Contracts\Exceptions;

interface HasErrorCodeInterface
{
    public function getStatusCode(): int;

    public function getErrorCode(): string;
}
END_OF_FILE_CONTENT_XQ9Z
echo "  ✓ stubs/Contracts/Exceptions/HasErrorCodeInterface.php"

mkdir -p "$(dirname "./stubs/Dtos/BaseDto.php")"
cat > "./stubs/Dtos/BaseDto.php" << 'END_OF_FILE_CONTENT_XQ9Z'
<?php

declare(strict_types=1);

namespace App\Dtos;

abstract class BaseDto
{
    //
}
END_OF_FILE_CONTENT_XQ9Z
echo "  ✓ stubs/Dtos/BaseDto.php"

mkdir -p "$(dirname "./stubs/Exceptions/AppExceptionHandler.php")"
cat > "./stubs/Exceptions/AppExceptionHandler.php" << 'END_OF_FILE_CONTENT_XQ9Z'
<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Contracts\Exceptions\HasErrorCodeInterface;
use App\Http\Controllers\BaseController;
use App\Logging\Contracts\ActivityLoggerInterface;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Register this handler in bootstrap/app.php:
 *
 *   ->withExceptions(function (Exceptions $exceptions) {
 *       AppExceptionHandler::register($exceptions);
 *   })
 */
class AppExceptionHandler
{
    public static function register(Exceptions $exceptions): void
    {
        static::configureReporting($exceptions);
        static::configureRendering($exceptions);
    }

    private static function configureReporting(Exceptions $exceptions): void
    {
        $exceptions->dontReport([
            ValidationException::class,
        ]);

        $exceptions->report(function (Throwable $e) {
            app(ActivityLoggerInterface::class)->error($e->getMessage(), [
                'exception' => get_class($e),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'url'       => request()->fullUrl(),
                'trace'     => $e->getTraceAsString(),
            ]);

            return false; // Let Laravel continue its default reporting
        });
    }

    private static function configureRendering(Exceptions $exceptions): void
    {
        $exceptions->render(function (Throwable $e, Request $request): ?Response {
            $expectsJson = $request->expectsJson() || $request->is('api/*');

            if ($e instanceof ValidationException) {
                if ($expectsJson) {
                    return app(BaseController::class)->toApiError($e);
                }

                return null; // Let Laravel handle Blade validation errors
            }

            if ($e instanceof HasErrorCodeInterface) {
                if ($expectsJson) {
                    return app(BaseController::class)->toApiError($e);
                }

                if ($e->getStatusCode() === 404) {
                    return response()->view('errors.404', [], 404);
                }

                return redirect()->back()->with('error', $e->getMessage());
            }

            if ($expectsJson) {
                return app(BaseController::class)->toApiError($e);
            }

            app(BaseController::class)->handleException($e);

            return redirect()->back();
        });
    }
}
END_OF_FILE_CONTENT_XQ9Z
echo "  ✓ stubs/Exceptions/AppExceptionHandler.php"

mkdir -p "$(dirname "./stubs/Exceptions/DomainException.php")"
cat > "./stubs/Exceptions/DomainException.php" << 'END_OF_FILE_CONTENT_XQ9Z'
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
END_OF_FILE_CONTENT_XQ9Z
echo "  ✓ stubs/Exceptions/DomainException.php"

mkdir -p "$(dirname "./stubs/Http/Controllers/BaseController.php")"
cat > "./stubs/Http/Controllers/BaseController.php" << 'END_OF_FILE_CONTENT_XQ9Z'
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\Exceptions\HasErrorCodeInterface;
use ErrorException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as Http;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Throwable;

class BaseController extends Controller
{
    public function handleException(Throwable $e): void
    {
        if ($e instanceof ValidationException) {
            return;
        }

        $userMessage = $this->getUserMessage($e);
        session()->flash('error', $userMessage);
    }

    protected function getUserMessage(Throwable $exception): string
    {
        return match (true) {
            $exception instanceof QueryException         => __('exception.database_error'),
            $exception instanceof AuthenticationException => __('exception.authentication_error'),
            $exception instanceof AuthorizationException  => __('exception.authorization_error'),
            $exception instanceof ModelNotFoundException  => __('exception.model_not_found'),
            $exception instanceof FileNotFoundException   => __('exception.file_not_found'),
            $exception instanceof HttpException && $exception->getStatusCode() === 500 => __('exception.internal_error'),
            $exception instanceof HttpException          => $exception->getMessage(),
            $exception instanceof ErrorException         => __('exception.unexpected_error'),
            default                                      => __('exception.unexpected_error'),
        };
    }

    public function getStatusCode(Throwable $e): int
    {
        if ($e instanceof HasErrorCodeInterface) {
            return $e->getStatusCode();
        }

        return match (true) {
            $e instanceof ValidationException          => Http::HTTP_UNPROCESSABLE_ENTITY,
            $e instanceof AuthenticationException      => Http::HTTP_UNAUTHORIZED,
            $e instanceof AuthorizationException       => Http::HTTP_FORBIDDEN,
            $e instanceof ModelNotFoundException       => Http::HTTP_NOT_FOUND,
            $e instanceof FileNotFoundException        => Http::HTTP_NOT_FOUND,
            $e instanceof MethodNotAllowedException    => Http::HTTP_METHOD_NOT_ALLOWED,
            $e instanceof TooManyRequestsHttpException => Http::HTTP_TOO_MANY_REQUESTS,
            $e instanceof HttpException                => $e->getStatusCode(),
            default                                    => Http::HTTP_INTERNAL_SERVER_ERROR,
        };
    }

    public function toApiError(Throwable $e): \Illuminate\Http\JsonResponse
    {
        $status    = $this->getStatusCode($e);
        $errorCode = $e instanceof HasErrorCodeInterface ? $e->getErrorCode() : null;

        if ($e instanceof ValidationException) {
            return response()->error(
                422,
                $e->getMessage(),
                $this->buildRuleBasedErrorBags($e),
                'validation.failed',
            );
        }

        if ($e instanceof HttpException) {
            return response()->error(
                $e->getStatusCode(),
                $e->getMessage(),
                null,
                $errorCode ?? "http.{$status}",
            );
        }

        if ($e instanceof HasErrorCodeInterface) {
            return response()->error(
                $status,
                $e->getMessage(),
                null,
                $errorCode,
            );
        }

        return response()->error(
            $status,
            $this->getUserMessage($e),
            null,
            $errorCode,
        );
    }

    /**
     * @return array<string, array<string, string>>
     */
    protected function buildRuleBasedErrorBags(ValidationException $e): array
    {
        $messages   = $e->errors();
        $failed     = $e->validator->failed();
        $normalized = [];

        foreach ($messages as $field => $fieldMessages) {
            $fieldFailed = $failed[$field] ?? [];
            $rules       = array_keys($fieldFailed);

            foreach ($fieldMessages as $index => $message) {
                $rule                          = $rules[$index] ?? 'Rule_' . $index;
                $normalized[$field][Str::lower((string) $rule)] = $message;
            }
        }

        return $normalized;
    }
}
END_OF_FILE_CONTENT_XQ9Z
echo "  ✓ stubs/Http/Controllers/BaseController.php"

mkdir -p "$(dirname "./stubs/Http/Macros/ResponseMacros.php")"
cat > "./stubs/Http/Macros/ResponseMacros.php" << 'END_OF_FILE_CONTENT_XQ9Z'
<?php

declare(strict_types=1);

namespace App\Http\Macros;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\ResponseFactory;

class ResponseMacros
{
    public static function register(): void
    {
        /** @var ResponseFactory $factory */
        $factory = app(ResponseFactory::class);

        $factory->macro('success', function (
            int $statusCode,
            mixed $data = null,
            ?string $message = null,
        ) use ($factory): JsonResponse {
            return $factory->json([
                'statusCode' => $statusCode,
                'error'      => false,
                'message'    => $message,
                'errorCode'  => null,
                'errorBags'  => null,
                'data'       => $data,
            ], $statusCode);
        });

        $factory->macro('error', function (
            int $statusCode,
            ?string $errorMessage = null,
            ?array $errorBags = [],
            ?string $errorCode = null,
        ) use ($factory): JsonResponse {
            $fallbackCode = match (true) {
                $statusCode === 422              => 'validation.failed',
                $statusCode === 401              => 'http.401',
                $statusCode === 403              => 'http.403',
                $statusCode === 404              => 'http.404',
                $statusCode === 405              => 'http.405',
                $statusCode === 429              => 'http.429',
                $statusCode >= 500               => 'exception.unexpected',
                default                          => "http.{$statusCode}",
            };

            return $factory->json([
                'statusCode' => $statusCode,
                'error'      => true,
                'message'    => $errorMessage,
                'errorCode'  => $errorCode ?? $fallbackCode,
                'errorBags'  => $errorBags,
                'data'       => null,
            ], $statusCode);
        });
    }
}
END_OF_FILE_CONTENT_XQ9Z
echo "  ✓ stubs/Http/Macros/ResponseMacros.php"

mkdir -p "$(dirname "./stubs/Http/Resources/BaseResource.php")"
cat > "./stubs/Http/Resources/BaseResource.php" << 'END_OF_FILE_CONTENT_XQ9Z'
<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

abstract class BaseResource extends JsonResource
{
    /**
     * BaseResource accepts a Result DTO, not an Eloquent model.
     *
     * @param mixed $resource
     */
    public function __construct(mixed $resource)
    {
        parent::__construct($resource);
    }
}
END_OF_FILE_CONTENT_XQ9Z
echo "  ✓ stubs/Http/Resources/BaseResource.php"

mkdir -p "$(dirname "./stubs/Http/ViewModels/BaseViewModel.php")"
cat > "./stubs/Http/ViewModels/BaseViewModel.php" << 'END_OF_FILE_CONTENT_XQ9Z'
<?php

declare(strict_types=1);

namespace App\Http\ViewModels;

use App\Dtos\BaseDto;

abstract class BaseViewModel
{
    public function __construct(
        protected readonly BaseDto $dto,
    ) {}

    /**
     * Returns data to be passed to the Blade view.
     *
     * @return array<string, mixed>
     */
    abstract public function toArray(): array;
}
END_OF_FILE_CONTENT_XQ9Z
echo "  ✓ stubs/Http/ViewModels/BaseViewModel.php"

mkdir -p "$(dirname "./stubs/Logging/ActivityLogger.php")"
cat > "./stubs/Logging/ActivityLogger.php" << 'END_OF_FILE_CONTENT_XQ9Z'
<?php

declare(strict_types=1);

namespace App\Logging;

use App\Logging\Contracts\ActivityLoggerInterface;
use Illuminate\Support\Facades\Auth;

class ActivityLogger implements ActivityLoggerInterface
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
END_OF_FILE_CONTENT_XQ9Z
echo "  ✓ stubs/Logging/ActivityLogger.php"

mkdir -p "$(dirname "./stubs/Logging/Contracts/ActivityLoggerInterface.php")"
cat > "./stubs/Logging/Contracts/ActivityLoggerInterface.php" << 'END_OF_FILE_CONTENT_XQ9Z'
<?php

declare(strict_types=1);

namespace App\Logging\Contracts;

interface ActivityLoggerInterface
{
    public function info(string $message, array $context = []): void;

    public function warning(string $message, array $context = []): void;

    public function error(string $message, array $context = []): void;
}
END_OF_FILE_CONTENT_XQ9Z
echo "  ✓ stubs/Logging/Contracts/ActivityLoggerInterface.php"

mkdir -p "$(dirname "./stubs/Models/ActivityLog.php")"
cat > "./stubs/Models/ActivityLog.php" << 'END_OF_FILE_CONTENT_XQ9Z'
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'logs';

    protected $fillable = [
        'level',
        'message',
        'context',
        'url',
        'ip',
        'user_id',
    ];

    protected $casts = [
        'context'    => 'array',
        'created_at' => 'datetime',
    ];
}
END_OF_FILE_CONTENT_XQ9Z
echo "  ✓ stubs/Models/ActivityLog.php"

mkdir -p "$(dirname "./stubs/Providers/AppServiceProvider.php")"
cat > "./stubs/Providers/AppServiceProvider.php" << 'END_OF_FILE_CONTENT_XQ9Z'
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
END_OF_FILE_CONTENT_XQ9Z
echo "  ✓ stubs/Providers/AppServiceProvider.php"

mkdir -p "$(dirname "./tests/Pest.php")"
cat > "./tests/Pest.php" << 'END_OF_FILE_CONTENT_XQ9Z'
<?php

declare(strict_types=1);

use function Pest\Laravel\uses;

uses()->in('tests');
END_OF_FILE_CONTENT_XQ9Z
echo "  ✓ tests/Pest.php"

cp "$0" "./sync.sh"
echo "  ✓ sync.sh"

echo ""
echo "Done — all files synced to $TARGET"