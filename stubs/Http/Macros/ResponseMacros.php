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
