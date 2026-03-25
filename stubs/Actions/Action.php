<?php

declare(strict_types=1);

namespace App\Actions;

/**
 * Base class for all Actions.
 *
 * Each Action represents a single application use case.
 * It must be final, implement execute(), and return one of:
 *
 *   Dto               — single typed payload
 *   CollectionResult  — non-paginated list of DTOs
 *   PaginatedResult   — paginated list of DTOs with metadata
 *   void              — side-effect only (delete, dispatch, toggle)
 *
 * Actions must not return: arrays, Eloquent models, Eloquent collections,
 * paginator instances, or HTTP responses. These are enforced by arch tests.
 */
abstract class Action
{
    public static function run(mixed ...$arguments): mixed
    {
        return app(static::class)->execute(...$arguments);
    }

    abstract public function execute(): mixed;
}
