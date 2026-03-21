<?php

declare(strict_types=1);

namespace App\Data;

use App\Contracts\Dto\ResultData;

/**
 * Wraps a typed list of Result DTOs returned from an Action.
 *
 * Usage:
 *   return new CollectionResult(
 *       items: $courses->map(fn($c) => new CourseItemDto(...))->all(),
 *   );
 *
 * @template T of ResultData
 */
final class CollectionResult
{
    /**
     * @param array<int, T> $items
     */
    public function __construct(
        public readonly array $items,
    ) {}
}
