<?php

declare(strict_types=1);

namespace App\Dtos\Common;

use App\Dtos\Dto;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Wraps a paginated list of DTOs returned from an Action.
 *
 * The static factory maps models → DTOs inside the Action and extracts
 * pagination metadata, preventing the paginator from leaking past the Action boundary.
 *
 * Usage:
 *   return PaginatedResult::fromPaginator(
 *       paginator: Course::query()->paginate($dto->perPage),
 *       mapper: fn($course) => new CourseData(
 *           id: $course->id,
 *           title: $course->title,
 *       ),
 *   );
 *
 * @template T of Dto
 */
final class PaginatedResult
{
    /**
     * @param array<int, T> $items
     */
    public function __construct(
        public readonly array $items,
        public readonly int   $total,
        public readonly int   $perPage,
        public readonly int   $currentPage,
        public readonly int   $lastPage,
    ) {}

    /**
     * @template T of Dto
     * @param  Closure(mixed): T  $mapper
     * @return self<T>
     */
    public static function fromPaginator(LengthAwarePaginator $paginator, Closure $mapper): self
    {
        return new self(
            items:       array_map($mapper, $paginator->items()),
            total:       $paginator->total(),
            perPage:     $paginator->perPage(),
            currentPage: $paginator->currentPage(),
            lastPage:    $paginator->lastPage(),
        );
    }
}
