<?php

declare(strict_types=1);

namespace App\Contracts\Dto;

/**
 * Marker interface implemented by all *ResultDto classes.
 *
 * ResultData objects are the typed output of Actions.
 * They are never Eloquent models — only plain data carriers.
 * Convention: class CourseResultDto implements ResultData { ... }
 */
interface ResultData
{
}
