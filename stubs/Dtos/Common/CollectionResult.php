<?php

declare(strict_types=1);

namespace App\Dtos\Common;

use App\Dtos\Dto;

/**
 * Wraps a typed list of DTOs returned from an Action.
 *
 * Usage:
 *   return new CollectionResult(
 *       items: $courses->map(fn($c) => new CourseData(...))->all(),
 *   );
 *
 * @template T of Dto
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
