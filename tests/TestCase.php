<?php

declare(strict_types=1);

namespace Antroly\Foundation\Tests;

use Antroly\Foundation\FoundationServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            FoundationServiceProvider::class,
        ];
    }
}
