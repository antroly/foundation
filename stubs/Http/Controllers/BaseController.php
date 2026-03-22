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
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

class BaseController extends Controller
{
    public function handleException(Throwable $e): void
    {
        if ($e instanceof ValidationException) {
            return;
        }

        session()->flash('error', $this->getUserMessage($e));
    }

    protected function getUserMessage(Throwable $exception): string
    {
        $message = match (true) {
            $exception instanceof QueryException          => __('exception.database_error'),
            $exception instanceof AuthenticationException => __('exception.authentication_error'),
            $exception instanceof AuthorizationException  => __('exception.authorization_error'),
            $exception instanceof ModelNotFoundException  => __('exception.model_not_found'),
            $exception instanceof FileNotFoundException   => __('exception.file_not_found'),
            $exception instanceof HttpException && $exception->getStatusCode() === 500 => __('exception.internal_error'),
            $exception instanceof HttpException           => $exception->getMessage(),
            $exception instanceof ErrorException          => __('exception.unexpected_error'),
            default                                       => __('exception.unexpected_error'),
        };

        return is_string($message) ? $message : '';
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
            $e instanceof MethodNotAllowedHttpException => Http::HTTP_METHOD_NOT_ALLOWED,
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
                $rule                                           = $rules[$index] ?? 'Rule_' . $index;
                $normalized[$field][Str::lower((string) $rule)] = $message;
            }
        }

        return $normalized;
    }
}
