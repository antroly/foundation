<?php

declare(strict_types=1);

use Antroly\Foundation\Console\InstallCommand;

describe('antroly:install', function () {

    it('exits successfully', function () {
        $this->artisan(InstallCommand::class)
            ->expectsQuestion('Publish the activity log migration?', true)
            ->assertExitCode(0);
    });

    it('publishes foundation stubs and architecture tests without migration when declined', function () {
        $this->artisan(InstallCommand::class)
            ->expectsQuestion('Publish the activity log migration?', false)
            ->assertExitCode(0);
    });

    it('outputs next steps after install', function () {
        $this->artisan(InstallCommand::class)
            ->expectsQuestion('Publish the activity log migration?', false)
            ->expectsOutputToContain('AppServiceProvider')
            ->expectsOutputToContain('AppExceptionHandler')
            ->expectsOutputToContain('php artisan migrate')
            ->assertExitCode(0);
    });

});
