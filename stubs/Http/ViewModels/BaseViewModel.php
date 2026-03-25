<?php

declare(strict_types=1);

namespace App\Http\ViewModels;

use App\Dtos\Dto;

abstract class BaseViewModel
{
    public function __construct(
        protected readonly Dto $data,
    ) {}

    /**
     * Returns data to be passed to the Blade view.
     *
     * @return array<string, mixed>
     */
    abstract public function toArray(): array;
}
