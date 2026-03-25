<?php

declare(strict_types=1);

namespace App\Dtos;

/**
 * Base class for all DTOs.
 *
 * DTOs are pure typed data containers — no logic, no framework awareness.
 * Extend this class for all input and output data objects.
 *
 * Convention:
 *   Input  → CreateCourseData, ListCoursesData
 *   Output → CourseData, CourseListItemData
 *
 * Naming is application-level convention, not package-level enforcement.
 */
abstract class Dto
{
}
