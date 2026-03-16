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
        "illuminate/http": "^11.0|^12.0",
        "illuminate/routing": "^11.0|^12.0"
    },
    "require-dev": {
        "orchestra/testbench": "^9.0|^10.0",
        "pestphp/pest": "^3.0",
        "pestphp/pest-plugin-laravel": "^3.0",
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
    "scripts": {
        "pest": "pest",
        "test:coverage": "pest --coverage",
        "lint": "pint",
        "analyse": "phpstan analyse"
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

mkdir -p "$(dirname "./src/Console/Commands/MakeAction.php")"
cat > "./src/Console/Commands/MakeAction.php" << 'END_OF_FILE_CONTENT_XQ9Z'
<?php

declare(strict_types=1);

namespace Antroly\Foundation\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeAction extends Command
{
    protected $signature = 'make:action {name : The action name, e.g. Course/CreateCourse}';

    protected $description = 'Create a new Action with Submit and Result DTOs';

    public function handle(): int
    {
        $name = $this->argument('name');

        [$domain, $action] = $this->parseName($name);

        $this->generateAction($domain, $action);
        $this->generateSubmitDto($domain, $action);
        $this->generateResultDto($domain, $action);

        return self::SUCCESS;
    }

    private function parseName(string $name): array
    {
        if (str_contains($name, '/')) {
            [$domain, $action] = explode('/', $name, 2);
            return [Str::studly($domain), Str::studly($action)];
        }

        // No domain prefix — use the action name as both
        $action = Str::studly($name);
        return [$action, $action];
    }

    private function generateAction(string $domain, string $action): void
    {
        $className  = "{$action}Action";
        $submitDto  = "{$action}SubmitDto";
        $resultDto  = "{$action}ResultDto";
        $namespace  = "App\\Actions\\{$domain}";
        $dtoNs      = "App\\Dtos\\{$domain}";
        $path       = app_path("Actions/{$domain}/{$className}.php");

        $content = <<<PHP
        <?php

        declare(strict_types=1);

        namespace {$namespace};

        use App\Actions\Action;
        use {$dtoNs}\\{$submitDto};
        use {$dtoNs}\\{$resultDto};

        final class {$className} extends Action
        {
            public function execute({$submitDto} \$dto): {$resultDto}
            {
                // TODO: implement
            }
        }
        PHP;

        $this->writeFile($path, $content);
        $this->components->info("Action [{$path}] created successfully.");
    }

    private function generateSubmitDto(string $domain, string $action): void
    {
        $className = "{$action}SubmitDto";
        $namespace = "App\\Dtos\\{$domain}";
        $path      = app_path("Dtos/{$domain}/{$className}.php");

        $content = <<<PHP
        <?php

        declare(strict_types=1);

        namespace {$namespace};

        use App\Dtos\BaseDto;

        final class {$className} extends BaseDto
        {
            public function __construct(
                // TODO: add typed properties
            ) {}
        }
        PHP;

        $this->writeFile($path, $content);
        $this->components->info("SubmitDto [{$path}] created successfully.");
    }

    private function generateResultDto(string $domain, string $action): void
    {
        $className = "{$action}ResultDto";
        $namespace = "App\\Dtos\\{$domain}";
        $path      = app_path("Dtos/{$domain}/{$className}.php");

        $content = <<<PHP
        <?php

        declare(strict_types=1);

        namespace {$namespace};

        use App\Dtos\BaseDto;

        final class {$className} extends BaseDto
        {
            public function __construct(
                // TODO: add typed properties
            ) {}
        }
        PHP;

        $this->writeFile($path, $content);
        $this->components->info("ResultDto [{$path}] created successfully.");
    }

    private function writeFile(string $path, string $content): void
    {
        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        if (file_exists($path)) {
            $this->components->warn("File [{$path}] already exists. Skipping.");
            return;
        }

        file_put_contents($path, $content);
    }
}
END_OF_FILE_CONTENT_XQ9Z
echo "  ✓ src/Console/Commands/MakeAction.php"

mkdir -p "$(dirname "./src/Console/Commands/MakeException.php")"
cat > "./src/Console/Commands/MakeException.php" << 'END_OF_FILE_CONTENT_XQ9Z'
<?php

declare(strict_types=1);

namespace Antroly\Foundation\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeException extends Command
{
    protected $signature = 'make:domain-exception {name : The exception name, e.g. Course/CourseExpired}';

    protected $description = 'Create a new domain exception';

    public function handle(): int
    {
        $name = $this->argument('name');

        [$domain, $class] = $this->parseName($name);

        $className = Str::studly($class);

        if (! Str::endsWith($className, 'Exception')) {
            $className .= 'Exception';
        }

        $namespace = $domain
            ? "App\\Exceptions\\{$domain}"
            : 'App\\Exceptions';

        $path = $domain
            ? app_path("Exceptions/{$domain}/{$className}.php")
            : app_path("Exceptions/{$className}.php");

        $errorCode      = $this->toErrorCode($domain, $className);
        $defaultMessage = $this->toDefaultMessage($className);

        $content = <<<PHP
        <?php

        declare(strict_types=1);

        namespace {$namespace};

        use App\Exceptions\DomainException;

        final class {$className} extends DomainException
        {
            public function __construct()
            {
                parent::__construct(
                    '{$defaultMessage}',
                    422,
                    '{$errorCode}',
                );
            }
        }
        PHP;

        $this->writeFile($path, $content);
        $this->components->info("Exception [{$path}] created successfully.");

        return self::SUCCESS;
    }

    private function parseName(string $name): array
    {
        if (str_contains($name, '/')) {
            [$domain, $class] = explode('/', $name, 2);
            return [Str::studly($domain), $class];
        }

        return [null, $name];
    }

    private function toErrorCode(?string $domain, string $className): string
    {
        $base = Str::snake(Str::replaceLast('Exception', '', $className));

        return $domain
            ? Str::snake($domain) . '.' . $base
            : 'exception.' . $base;
    }

    private function toDefaultMessage(string $className): string
    {
        $base = Str::replaceLast('Exception', '', $className);

        // Split StudlyCase into words: CourseExpired -> Course expired
        $words = preg_replace('/([A-Z])/', ' $1', $base);

        return ucfirst(strtolower(trim($words))) . '.';
    }

    private function writeFile(string $path, string $content): void
    {
        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        if (file_exists($path)) {
            $this->components->warn("File [{$path}] already exists. Skipping.");
            return;
        }

        file_put_contents($path, $content);
    }
}
END_OF_FILE_CONTENT_XQ9Z
echo "  ✓ src/Console/Commands/MakeException.php"

mkdir -p "$(dirname "./src/Console/Commands/MakeRequest.php")"
cat > "./src/Console/Commands/MakeRequest.php" << 'END_OF_FILE_CONTENT_XQ9Z'
<?php

declare(strict_types=1);

namespace Antroly\Foundation\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeRequest extends Command
{
    protected $signature = 'make:antroly-request {name : The request name, e.g. Course/CreateCourseRequest}';

    protected $description = 'Create a new FormRequest that maps to a SubmitDto';

    public function handle(): int
    {
        $name = $this->argument('name');

        [$domain, $class] = $this->parseName($name);

        $className = Str::studly($class);
        $namespace = $domain ? "App\\Http\\Requests\\{$domain}" : 'App\\Http\\Requests';
        $dtoNs     = $domain ? "App\\Dtos\\{$domain}" : 'App\\Dtos';
        $submitDto = Str::replaceLast('Request', 'SubmitDto', $className);
        $path      = $domain
            ? app_path("Http/Requests/{$domain}/{$className}.php")
            : app_path("Http/Requests/{$className}.php");

        $content = <<<PHP
        <?php

        declare(strict_types=1);

        namespace {$namespace};

        use {$dtoNs}\\{$submitDto};
        use Illuminate\Foundation\Http\FormRequest;

        final class {$className} extends FormRequest
        {
            public function authorize(): bool
            {
                return true;
            }

            /**
             * @return array<string, mixed>
             */
            public function rules(): array
            {
                return [
                    // TODO: add validation rules
                ];
            }

            public function toDto(): {$submitDto}
            {
                return new {$submitDto}(
                    // TODO: map validated fields
                );
            }
        }
        PHP;

        $this->writeFile($path, $content);
        $this->components->info("Request [{$path}] created successfully.");

        return self::SUCCESS;
    }

    private function parseName(string $name): array
    {
        if (str_contains($name, '/')) {
            [$domain, $class] = explode('/', $name, 2);
            return [Str::studly($domain), $class];
        }

        return [null, $name];
    }

    private function writeFile(string $path, string $content): void
    {
        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        if (file_exists($path)) {
            $this->components->warn("File [{$path}] already exists. Skipping.");
            return;
        }

        file_put_contents($path, $content);
    }
}
END_OF_FILE_CONTENT_XQ9Z
echo "  ✓ src/Console/Commands/MakeRequest.php"

mkdir -p "$(dirname "./src/Console/Commands/MakeResource.php")"
cat > "./src/Console/Commands/MakeResource.php" << 'END_OF_FILE_CONTENT_XQ9Z'
<?php

declare(strict_types=1);

namespace Antroly\Foundation\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeResource extends Command
{
    protected $signature = 'make:antroly-resource {name : The resource name, e.g. Course/CourseResource}
                            {--web : Generate a ViewModel instead of an API Resource}';

    protected $description = 'Create a new Resource (API) or ViewModel (web)';

    public function handle(): int
    {
        $name = $this->argument('name');
        $web  = $this->option('web');

        [$domain, $class] = $this->parseName($name);

        $web
            ? $this->generateViewModel($domain, $class)
            : $this->generateResource($domain, $class);

        return self::SUCCESS;
    }

    private function parseName(string $name): array
    {
        if (str_contains($name, '/')) {
            [$domain, $class] = explode('/', $name, 2);
            return [Str::studly($domain), Str::studly($class)];
        }

        return [null, Str::studly($name)];
    }

    private function generateResource(?string $domain, string $class): void
    {
        $className = Str::endsWith($class, 'Resource') ? $class : "{$class}Resource";
        $namespace = $domain ? "App\\Http\\Resources\\{$domain}" : 'App\\Http\\Resources';
        $path      = $domain
            ? app_path("Http/Resources/{$domain}/{$className}.php")
            : app_path("Http/Resources/{$className}.php");

        $content = <<<PHP
        <?php

        declare(strict_types=1);

        namespace {$namespace};

        use App\Http\Resources\BaseResource;
        use Illuminate\Http\Request;

        final class {$className} extends BaseResource
        {
            /**
             * @return array<string, mixed>
             */
            public function toArray(Request \$request): array
            {
                return [
                    // TODO: map result DTO properties
                ];
            }
        }
        PHP;

        $this->writeFile($path, $content);
        $this->components->info("Resource [{$path}] created successfully.");
    }

    private function generateViewModel(?string $domain, string $class): void
    {
        $className = Str::endsWith($class, 'ViewModel') ? $class : "{$class}ViewModel";
        $namespace = $domain ? "App\\Http\\ViewModels\\{$domain}" : 'App\\Http\\ViewModels';
        $path      = $domain
            ? app_path("Http/ViewModels/{$domain}/{$className}.php")
            : app_path("Http/ViewModels/{$className}.php");

        $content = <<<PHP
        <?php

        declare(strict_types=1);

        namespace {$namespace};

        use App\Http\ViewModels\BaseViewModel;

        final class {$className} extends BaseViewModel
        {
            /**
             * @return array<string, mixed>
             */
            public function toArray(): array
            {
                return [
                    // TODO: map result DTO properties
                ];
            }
        }
        PHP;

        $this->writeFile($path, $content);
        $this->components->info("ViewModel [{$path}] created successfully.");
    }

    private function writeFile(string $path, string $content): void
    {
        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        if (file_exists($path)) {
            $this->components->warn("File [{$path}] already exists. Skipping.");
            return;
        }

        file_put_contents($path, $content);
    }
}
END_OF_FILE_CONTENT_XQ9Z
echo "  ✓ src/Console/Commands/MakeResource.php"

mkdir -p "$(dirname "./src/FoundationServiceProvider.php")"
cat > "./src/FoundationServiceProvider.php" << 'END_OF_FILE_CONTENT_XQ9Z'
<?php

declare(strict_types=1);

namespace Antroly\Foundation;

use Antroly\Foundation\Console\Commands\MakeAction;
use Antroly\Foundation\Console\Commands\MakeException;
use Antroly\Foundation\Console\Commands\MakeRequest;
use Antroly\Foundation\Console\Commands\MakeResource;
use Illuminate\Support\ServiceProvider;

class FoundationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

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
            __DIR__ . '/../stubs/Logging/Contracts/ActivityLoggerInterface.php'       => app_path('Logging/Contracts/ActivityLoggerInterface.php'),
            // Models
            __DIR__ . '/../stubs/Models/ActivityLog.php'                              => app_path('Models/ActivityLog.php'),
            // Providers
            __DIR__ . '/../stubs/Providers/AppServiceProvider.php'                    => app_path('Providers/AppServiceProvider.php'),
        ], 'antroly-foundation');

        $this->publishes([
            __DIR__ . '/../stubs/Tests/ArchitectureTest.php' => base_path('tests/ArchitectureTest.php'),
        ], 'antroly-tests');

        $this->publishes([
            __DIR__ . '/../database/migrations/create_logs_table.php' => database_path('migrations/' . date('Y_m_d_His') . '_create_logs_table.php'),
        ], 'antroly-migrations');
    }

    private function registerCommands(): void
    {
        $this->commands([
            MakeAction::class,
            MakeRequest::class,
            MakeResource::class,
            MakeException::class,
        ]);
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

mkdir -p "$(dirname "./stubs/Tests/ArchitectureTest.php")"
cat > "./stubs/Tests/ArchitectureTest.php" << 'END_OF_FILE_CONTENT_XQ9Z'
<?php

declare(strict_types=1);

describe('Architecture', function () {

    it('actions extend base Action class')
        ->expect('App\Actions')
        ->toExtend('App\Actions\Action');

    it('actions do not return Eloquent models')
        ->expect('App\Actions')
        ->not->toUse('Illuminate\Database\Eloquent\Model');

    it('dtos extend BaseDto')
        ->expect('App\Dtos')
        ->toExtend('App\Dtos\BaseDto');

    it('dtos do not depend on Eloquent models')
        ->expect('App\Dtos')
        ->not->toUse('Illuminate\Database\Eloquent\Model');

    it('resources extend BaseResource')
        ->expect('App\Http\Resources')
        ->toExtend('App\Http\Resources\BaseResource');

    it('resources do not depend on Eloquent models')
        ->expect('App\Http\Resources')
        ->not->toUse('Illuminate\Database\Eloquent\Model');

    it('controllers do not use Eloquent directly')
        ->expect('App\Http\Controllers')
        ->not->toUse('Illuminate\Database\Eloquent\Model');

    it('viewmodels extend BaseViewModel')
        ->expect('App\Http\ViewModels')
        ->toExtend('App\Http\ViewModels\BaseViewModel');

});
END_OF_FILE_CONTENT_XQ9Z
echo "  ✓ stubs/Tests/ArchitectureTest.php"

mkdir -p "$(dirname "./tests/Feature/ServiceProviderTest.php")"
cat > "./tests/Feature/ServiceProviderTest.php" << 'END_OF_FILE_CONTENT_XQ9Z'
<?php

declare(strict_types=1);

use Antroly\Foundation\FoundationServiceProvider;
use Illuminate\Support\ServiceProvider;

beforeEach(function () {
    $provider = new FoundationServiceProvider($this->app);
    $provider->boot();
});

describe('FoundationServiceProvider', function () {

    it('registers the antroly-foundation publish tag', function () {
        $publishes = ServiceProvider::pathsToPublish(
            FoundationServiceProvider::class,
            'antroly-foundation',
        );

        expect($publishes)->not->toBeEmpty();
    });

    it('registers the antroly-migrations publish tag', function () {
        $publishes = ServiceProvider::pathsToPublish(
            FoundationServiceProvider::class,
            'antroly-migrations',
        );

        expect($publishes)->not->toBeEmpty();
    });

    it('registers the antroly-tests publish tag', function () {
        $publishes = ServiceProvider::pathsToPublish(
            FoundationServiceProvider::class,
            'antroly-tests',
        );

        expect($publishes)->not->toBeEmpty();
    });

    it('publishes all expected foundation stubs', function () {
        $publishes = ServiceProvider::pathsToPublish(
            FoundationServiceProvider::class,
            'antroly-foundation',
        );

        $publishedFiles = array_map('basename', array_values($publishes));

        expect($publishedFiles)
            ->toContain('Action.php')
            ->toContain('BaseDto.php')
            ->toContain('HasErrorCodeInterface.php')
            ->toContain('DomainException.php')
            ->toContain('AppExceptionHandler.php')
            ->toContain('BaseController.php')
            ->toContain('ResponseMacros.php')
            ->toContain('BaseResource.php')
            ->toContain('BaseViewModel.php')
            ->toContain('ActivityLogger.php')
            ->toContain('ActivityLoggerInterface.php')
            ->toContain('ActivityLog.php')
            ->toContain('AppServiceProvider.php');
    });

    it('publishes the logs migration', function () {
        $publishes = ServiceProvider::pathsToPublish(
            FoundationServiceProvider::class,
            'antroly-migrations',
        );

        $sourceFiles = array_map('basename', array_keys($publishes));

        expect($sourceFiles)->toContain('create_logs_table.php');
    });

    it('publishes the architecture test', function () {
        $publishes = ServiceProvider::pathsToPublish(
            FoundationServiceProvider::class,
            'antroly-tests',
        );

        $sourceFiles = array_map('basename', array_keys($publishes));

        expect($sourceFiles)->toContain('ArchitectureTest.php');
    });

});
END_OF_FILE_CONTENT_XQ9Z
echo "  ✓ tests/Feature/ServiceProviderTest.php"

mkdir -p "$(dirname "./tests/Feature/StubsTest.php")"
cat > "./tests/Feature/StubsTest.php" << 'END_OF_FILE_CONTENT_XQ9Z'
<?php

declare(strict_types=1);

$stubsBase = realpath(__DIR__ . '/../../stubs');

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($stubsBase, RecursiveDirectoryIterator::SKIP_DOTS)
);

$dataset = [];
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $key = str_replace($stubsBase . '/', '', $file->getPathname());
        $dataset[$key] = [$file->getPathname()];
    }
}

it('stub is valid PHP: <filename>', function (string $path) {
    $output = shell_exec("php -l {$path} 2>&1");
    expect($output)->toContain('No syntax errors detected');
})->with($dataset);
END_OF_FILE_CONTENT_XQ9Z
echo "  ✓ tests/Feature/StubsTest.php"

mkdir -p "$(dirname "./tests/Pest.php")"
cat > "./tests/Pest.php" << 'END_OF_FILE_CONTENT_XQ9Z'
<?php

declare(strict_types=1);

use Antroly\Foundation\Tests\TestCase;

pest()->extend(TestCase::class)->in('Unit', 'Feature');
END_OF_FILE_CONTENT_XQ9Z
echo "  ✓ tests/Pest.php"

mkdir -p "$(dirname "./tests/TestCase.php")"
cat > "./tests/TestCase.php" << 'END_OF_FILE_CONTENT_XQ9Z'
<?php

declare(strict_types=1);

namespace Antroly\Foundation\Tests;

use Antroly\Foundation\FoundationServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            FoundationServiceProvider::class,
        ];
    }
}
END_OF_FILE_CONTENT_XQ9Z
echo "  ✓ tests/TestCase.php"

mkdir -p "$(dirname "./tests/Unit/ResponseMacrosTest.php")"
cat > "./tests/Unit/ResponseMacrosTest.php" << 'END_OF_FILE_CONTENT_XQ9Z'
<?php

declare(strict_types=1);

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\ResponseFactory;

beforeEach(function () {
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
            $statusCode === 422 => 'validation.failed',
            $statusCode === 401 => 'http.401',
            $statusCode === 403 => 'http.403',
            $statusCode === 404 => 'http.404',
            $statusCode === 405 => 'http.405',
            $statusCode === 429 => 'http.429',
            $statusCode >= 500  => 'exception.unexpected',
            default             => "http.{$statusCode}",
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
});

describe('response()->success()', function () {

    it('returns correct status code', function () {
        expect(response()->success(200, ['id' => 1])->getStatusCode())->toBe(200);
    });

    it('returns correct envelope structure', function () {
        $json = response()->success(200, ['id' => 1])->getData(true);

        expect($json)->toMatchArray([
            'statusCode' => 200,
            'error'      => false,
            'errorCode'  => null,
            'errorBags'  => null,
            'data'       => ['id' => 1],
        ]);
    });

    it('includes message when provided', function () {
        $json = response()->success(201, null, 'Created successfully')->getData(true);

        expect($json['message'])->toBe('Created successfully');
    });

    it('sets message to null when not provided', function () {
        $json = response()->success(200, ['id' => 1])->getData(true);

        expect($json['message'])->toBeNull();
    });

    it('sets data to null when not provided', function () {
        $json = response()->success(204)->getData(true);

        expect($json['data'])->toBeNull();
    });

});

describe('response()->error()', function () {

    it('returns correct status code', function () {
        expect(response()->error(422, 'Validation failed')->getStatusCode())->toBe(422);
    });

    it('returns correct envelope structure', function () {
        $json = response()->error(422, 'Validation failed', ['field' => ['required' => 'Required']], 'validation.failed')->getData(true);

        expect($json)->toMatchArray([
            'statusCode' => 422,
            'error'      => true,
            'message'    => 'Validation failed',
            'errorCode'  => 'validation.failed',
            'data'       => null,
        ]);
    });

    it('sets data to null always', function () {
        $json = response()->error(500, 'Server error')->getData(true);

        expect($json['data'])->toBeNull();
    });

});

describe('response()->error() fallback error codes', function () {

    it('uses validation.failed for 422', function () {
        expect(response()->error(422)->getData(true)['errorCode'])->toBe('validation.failed');
    });

    it('uses http.401 for 401', function () {
        expect(response()->error(401)->getData(true)['errorCode'])->toBe('http.401');
    });

    it('uses http.403 for 403', function () {
        expect(response()->error(403)->getData(true)['errorCode'])->toBe('http.403');
    });

    it('uses http.404 for 404', function () {
        expect(response()->error(404)->getData(true)['errorCode'])->toBe('http.404');
    });

    it('uses http.405 for 405', function () {
        expect(response()->error(405)->getData(true)['errorCode'])->toBe('http.405');
    });

    it('uses http.429 for 429', function () {
        expect(response()->error(429)->getData(true)['errorCode'])->toBe('http.429');
    });

    it('uses exception.unexpected for 500', function () {
        expect(response()->error(500)->getData(true)['errorCode'])->toBe('exception.unexpected');
    });

    it('uses exception.unexpected for any 5xx', function () {
        expect(response()->error(503)->getData(true)['errorCode'])->toBe('exception.unexpected');
    });

    it('uses dynamic http.{code} for other codes', function () {
        expect(response()->error(409)->getData(true)['errorCode'])->toBe('http.409');
    });

    it('uses provided errorCode over fallback', function () {
        expect(response()->error(422, 'Error', [], 'custom.code')->getData(true)['errorCode'])->toBe('custom.code');
    });

});
END_OF_FILE_CONTENT_XQ9Z
echo "  ✓ tests/Unit/ResponseMacrosTest.php"

cp "$0" "./sync.sh"
echo "  ✓ sync.sh"

echo ""
echo "Done — all files synced to $TARGET"