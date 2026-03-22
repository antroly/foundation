<?php

declare(strict_types=1);

use Antroly\Foundation\Console\MakeActionCommand;

describe('make:action', function () {

    it('exits successfully', function () {
        $this->artisan(MakeActionCommand::class, ['name' => 'Course/CreateCourse'])
            ->assertExitCode(0);
    });

    it('outputs action creation message', function () {
        $this->artisan(MakeActionCommand::class, ['name' => 'Course/CreateCourse'])
            ->expectsOutputToContain('Actions/Course/CreateCourseAction.php')
            ->assertExitCode(0);
    });

    it('outputs test creation message', function () {
        $this->artisan(MakeActionCommand::class, ['name' => 'Course/CreateCourse'])
            ->expectsOutputToContain('Actions/Course/CreateCourseActionTest.php')
            ->assertExitCode(0);
    });

    it('warns when action file already exists', function () {
        $this->artisan(MakeActionCommand::class, ['name' => 'Course/CreateCourse'])
            ->assertExitCode(0);

        $this->artisan(MakeActionCommand::class, ['name' => 'Course/CreateCourse'])
            ->expectsOutputToContain('already exists')
            ->assertExitCode(0);
    });

    it('fails when name has no domain prefix', function () {
        $this->artisan(MakeActionCommand::class, ['name' => 'CreateCourse'])
            ->expectsOutputToContain('Action name must include a domain')
            ->assertExitCode(1);
    });

});
