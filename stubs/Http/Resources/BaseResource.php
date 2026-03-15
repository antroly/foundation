<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

abstract class BaseResource extends JsonResource
{
    /**
     * BaseResource accepts a Result DTO, not an Eloquent model.
     *
     * @param mixed $resource
     */
    public function __construct(mixed $resource)
    {
        parent::__construct($resource);
    }
}
