<?php

declare(strict_types=1);

use Antroly\Foundation\Console\MakeActionResourceCommand;

describe('make:action-resource', function () {

    it('creates an API resource with domain prefix', function () {
        $this->artisan(MakeActionResourceCommand::class, ['name' => 'Course/Course'])
            ->expectsOutputToContain('Resources/Course/CourseResource.php')
            ->assertExitCode(0);
    });

    it('creates an API resource without domain prefix', function () {
        $this->artisan(MakeActionResourceCommand::class, ['name' => 'Course'])
            ->expectsOutputToContain('Resources/CourseResource.php')
            ->assertExitCode(0);
    });

    it('creates a ViewModel with --type=web', function () {
        $this->artisan(MakeActionResourceCommand::class, ['name' => 'Course/Course', '--type' => 'web'])
            ->expectsOutputToContain('ViewModels/Course/CourseViewModel.php')
            ->assertExitCode(0);
    });

    it('fails with an invalid --type value', function () {
        $this->artisan(MakeActionResourceCommand::class, ['name' => 'Course/Course', '--type' => 'invalid'])
            ->assertExitCode(1);
    });

    it('warns when file already exists', function () {
        $this->artisan(MakeActionResourceCommand::class, ['name' => 'Course/Course'])
            ->assertExitCode(0);

        $this->artisan(MakeActionResourceCommand::class, ['name' => 'Course/Course'])
            ->expectsOutputToContain('already exists')
            ->assertExitCode(0);
    });

});
