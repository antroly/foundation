<?php

declare(strict_types=1);

namespace App\Contracts\Dto;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Implemented by all *SubmitDto classes.
 *
 * Every SubmitDto must provide its own fromRequest() implementation.
 * Convention: $dto = CreateCourseSubmitDto::fromRequest($request);
 */
interface BuildsFromRequest
{
    public static function fromRequest(FormRequest $request): static;
}
