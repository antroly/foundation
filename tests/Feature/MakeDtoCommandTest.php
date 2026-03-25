<?php

declare(strict_types=1);

use Antroly\Foundation\Console\MakeDtoCommand;

describe('make:action-dto', function () {

    it('creates a Dto class', function () {
        $this->artisan(MakeDtoCommand::class, ['name' => 'Course/CreateCourseData'])
            ->expectsOutputToContain('Dtos/Course/CreateCourseData.php')
            ->assertExitCode(0);
    });

    it('creates a Dto without domain', function () {
        $this->artisan(MakeDtoCommand::class, ['name' => 'CreateCourseData'])
            ->expectsOutputToContain('Dtos/CreateCourseData.php')
            ->assertExitCode(0);
    });

    it('warns when file already exists', function () {
        $this->artisan(MakeDtoCommand::class, ['name' => 'Course/CreateCourseData'])
            ->assertExitCode(0);

        $this->artisan(MakeDtoCommand::class, ['name' => 'Course/CreateCourseData'])
            ->expectsOutputToContain('already exists')
            ->assertExitCode(0);
    });

});
