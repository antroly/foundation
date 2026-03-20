<?php

declare(strict_types=1);

use Antroly\Foundation\FoundationServiceProvider;
use Illuminate\Support\ServiceProvider;

describe('FoundationServiceProvider', function () {

    it('registers the antroly-foundation publish tag', function () {
        $publishes = ServiceProvider::pathsToPublish(
            FoundationServiceProvider::class,
            'antroly-foundation',
        );

        expect($publishes)->not->toBeEmpty();
    });

    it('registers the antroly-migrations publish tag', function () {
        $publishes = ServiceProvider::pathsToPublish(
            FoundationServiceProvider::class,
            'antroly-migrations',
        );

        expect($publishes)->not->toBeEmpty();
    });

    it('publishes all expected foundation stubs', function () {
        $publishes = ServiceProvider::pathsToPublish(
            FoundationServiceProvider::class,
            'antroly-foundation',
        );

        $publishedFiles = array_map('basename', array_values($publishes));

        expect($publishedFiles)
            ->toContain('Action.php')
            ->toContain('BuildsFromRequest.php')
            ->toContain('ResultData.php')
            ->toContain('HasErrorCodeInterface.php')
            ->toContain('DomainException.php')
            ->toContain('AppExceptionHandler.php')
            ->toContain('BaseController.php')
            ->toContain('ResponseMacros.php')
            ->toContain('BaseResource.php')
            ->toContain('BaseViewModel.php')
            ->toContain('AppLogger.php')
            ->toContain('DatabaseLogger.php')
            ->toContain('ActivityLog.php')
            ->toContain('AppServiceProvider.php');
    });

    it('registers the antroly-tests publish tag', function () {
        $publishes = ServiceProvider::pathsToPublish(
            FoundationServiceProvider::class,
            'antroly-tests',
        );

        expect($publishes)->not->toBeEmpty();
    });

    it('publishes the architecture test stub', function () {
        $publishes = ServiceProvider::pathsToPublish(
            FoundationServiceProvider::class,
            'antroly-tests',
        );

        $sourceFiles = array_map('basename', array_keys($publishes));

        expect($sourceFiles)->toContain('ArchitectureTest.php');
    });

    it('publishes the logs migration', function () {
        $publishes = ServiceProvider::pathsToPublish(
            FoundationServiceProvider::class,
            'antroly-migrations',
        );

        $sourceFiles = array_map('basename', array_keys($publishes));

        expect($sourceFiles)->toContain('create_logs_table.php');
    });

});