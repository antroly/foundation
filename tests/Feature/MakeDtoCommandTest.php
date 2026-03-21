<?php

declare(strict_types=1);

use Antroly\Foundation\Console\MakeDtoCommand;

describe('make:dto', function () {

    it('creates a SubmitDto by default', function () {
        $this->artisan(MakeDtoCommand::class, ['name' => 'Course/CreateCourse'])
            ->expectsOutputToContain('Dtos/Course/CreateCourseSubmitDto.php')
            ->assertExitCode(0);
    });

    it('creates a SubmitDto with --type=submit', function () {
        $this->artisan(MakeDtoCommand::class, ['name' => 'Course/CreateCourse', '--type' => 'submit'])
            ->expectsOutputToContain('Dtos/Course/CreateCourseSubmitDto.php')
            ->assertExitCode(0);
    });

    it('creates a ResultDto with --type=result', function () {
        $this->artisan(MakeDtoCommand::class, ['name' => 'Course/CreateCourse', '--type' => 'result'])
            ->expectsOutputToContain('Dtos/Course/CreateCourseResultDto.php')
            ->assertExitCode(0);
    });

    it('fails with an invalid type', function () {
        $this->artisan(MakeDtoCommand::class, ['name' => 'Course/CreateCourse', '--type' => 'invalid'])
            ->assertExitCode(1);
    });

    it('warns when file already exists', function () {
        $this->artisan(MakeDtoCommand::class, ['name' => 'Course/CreateCourse', '--type' => 'result'])
            ->assertExitCode(0);

        $this->artisan(MakeDtoCommand::class, ['name' => 'Course/CreateCourse', '--type' => 'result'])
            ->expectsOutputToContain('already exists')
            ->assertExitCode(0);
    });

});
