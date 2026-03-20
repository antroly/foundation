<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Contracts\Dto\ResultData;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class BaseResource extends JsonResource
{
    /**
     * BaseResource accepts a ResultData DTO, not an Eloquent model.
     * Pipeline: Action -> ResultData -> Resource.
     */
    public function __construct(ResultData $resource)
    {
        parent::__construct($resource);
    }
}
