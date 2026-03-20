<?php

declare(strict_types=1);

namespace App\Actions;

abstract class Action
{
    public static function run(mixed ...$arguments): mixed
    {
        return app(static::class)->execute(...$arguments);
    }

    abstract public function execute(): mixed;
}
