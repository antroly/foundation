<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Contracts\Exceptions\HasErrorCodeInterface;
use App\Http\Controllers\BaseController;
use App\Logging\Contracts\AppLogger;
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

    protected static function configureReporting(Exceptions $exceptions): void
    {
        $exceptions->dontReport([
            ValidationException::class,
        ]);

        $exceptions->report(function (Throwable $e) {
            app(AppLogger::class)->error($e->getMessage(), [
                'exception' => get_class($e),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'url'       => request()->fullUrl(),
                'trace'     => $e->getTraceAsString(),
            ]);

            return false;
        });
    }

    protected static function configureRendering(Exceptions $exceptions): void
    {
        $exceptions->render(function (Throwable $e, Request $request): ?Response {
            $expectsJson = $request->expectsJson() || $request->is('api/*');

            if ($e instanceof ValidationException) {
                if ($expectsJson) {
                    return app(BaseController::class)->toApiError($e);
                }

                return null;
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
