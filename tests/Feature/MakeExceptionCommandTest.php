<?php

declare(strict_types=1);

use Antroly\Foundation\Console\MakeExceptionCommand;

describe('make:domain-exception', function () {

    it('creates an exception with domain prefix', function () {
        $this->artisan(MakeExceptionCommand::class, ['name' => 'Course/CourseExpired'])
            ->expectsOutputToContain('Exceptions/Course/CourseExpiredException.php')
            ->assertExitCode(0);
    });

    it('creates an exception without domain prefix', function () {
        $this->artisan(MakeExceptionCommand::class, ['name' => 'CourseExpired'])
            ->expectsOutputToContain('Exceptions/CourseExpiredException.php')
            ->assertExitCode(0);
    });

    it('appends Exception suffix when missing', function () {
        $this->artisan(MakeExceptionCommand::class, ['name' => 'Course/CourseExpired'])
            ->expectsOutputToContain('CourseExpiredException.php')
            ->assertExitCode(0);
    });

    it('does not double-append Exception suffix', function () {
        $this->artisan(MakeExceptionCommand::class, ['name' => 'Course/CourseExpiredException'])
            ->expectsOutputToContain('Exceptions/Course/CourseExpiredException.php')
            ->assertExitCode(0);
    });

    it('warns when file already exists', function () {
        $this->artisan(MakeExceptionCommand::class, ['name' => 'Course/CourseExpired'])
            ->assertExitCode(0);

        $this->artisan(MakeExceptionCommand::class, ['name' => 'Course/CourseExpired'])
            ->expectsOutputToContain('already exists')
            ->assertExitCode(0);
    });

});
