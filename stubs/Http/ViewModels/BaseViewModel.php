<?php

declare(strict_types=1);

namespace App\Http\ViewModels;

use App\Contracts\Dto\ResultData;

abstract class BaseViewModel
{
    public function __construct(
        protected readonly ResultData $data,
    ) {}

    /**
     * Returns data to be passed to the Blade view.
     *
     * @return array<string, mixed>
     */
    abstract public function toArray(): array;
}
