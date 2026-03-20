<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

abstract class BaseResource extends JsonResource
{
    /**
     * Intentionally accepts mixed — the strict type hint fights Laravel internals.
     * Convention: always pass a ResultData DTO. Pipeline: Action → ResultData → Resource.
     * Enforce the boundary via architecture tests, not the constructor.
     */
    public function __construct(mixed $resource)
    {
        parent::__construct($resource);
    }
}
