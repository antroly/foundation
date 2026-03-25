<?php

declare(strict_types=1);

use Antroly\Foundation\Console\MakeActionRequestCommand;

describe('make:action-request', function () {

    it('creates a request with domain prefix', function () {
        $this->artisan(MakeActionRequestCommand::class, ['name' => 'Course/CreateCourse'])
            ->expectsOutputToContain('Requests/Course/CreateCourseRequest.php')
            ->assertExitCode(0);
    });

    it('creates a request without domain prefix', function () {
        $this->artisan(MakeActionRequestCommand::class, ['name' => 'CreateCourse'])
            ->expectsOutputToContain('Requests/CreateCourseRequest.php')
            ->assertExitCode(0);
    });

    it('warns when file already exists', function () {
        $this->artisan(MakeActionRequestCommand::class, ['name' => 'Course/CreateCourse'])
            ->assertExitCode(0);

        $this->artisan(MakeActionRequestCommand::class, ['name' => 'Course/CreateCourse'])
            ->expectsOutputToContain('already exists')
            ->assertExitCode(0);
    });

});
