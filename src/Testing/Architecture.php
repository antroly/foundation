<?php

declare(strict_types=1);

namespace Antroly\Foundation\Testing;

use Pest\Arch\Contracts\ArchExpectation;

class Architecture
{
    public static function actions(): ArchExpectation
    {
        return arch('Actions extend base Action class')
            ->expect('App\Actions')
            ->toExtend('App\Actions\Action');
    }

    public static function actionsReturnDtos(): ArchExpectation
    {
        return arch('Actions must not return Eloquent models')
            ->expect('App\Actions')
            ->not->toUse('Illuminate\Database\Eloquent\Model');
    }

    public static function dtos(): ArchExpectation
    {
        return arch('DTOs extend BaseDto')
            ->expect('App\Dtos')
            ->toExtend('App\Dtos\BaseDto');
    }

    public static function dtosAreClean(): ArchExpectation
    {
        return arch('DTOs must not depend on Eloquent models')
            ->expect('App\Dtos')
            ->not->toUse('Illuminate\Database\Eloquent\Model');
    }

    public static function resources(): ArchExpectation
    {
        return arch('Resources extend BaseResource')
            ->expect('App\Http\Resources')
            ->toExtend('App\Http\Resources\BaseResource');
    }

    public static function resourcesAreClean(): ArchExpectation
    {
        return arch('Resources must not depend on Eloquent models')
            ->expect('App\Http\Resources')
            ->not->toUse('Illuminate\Database\Eloquent\Model');
    }

    public static function controllers(): ArchExpectation
    {
        return arch('Controllers must not use Eloquent directly')
            ->expect('App\Http\Controllers')
            ->not->toUse('Illuminate\Database\Eloquent\Model');
    }

    public static function viewModels(): ArchExpectation
    {
        return arch('ViewModels extend BaseViewModel')
            ->expect('App\Http\ViewModels')
            ->toExtend('App\Http\ViewModels\BaseViewModel');
    }

    /**
     * Run all architecture rules at once.
     */
    public static function all(): void
    {
        static::actions();
        static::actionsReturnDtos();
        static::dtos();
        static::dtosAreClean();
        static::resources();
        static::resourcesAreClean();
        static::controllers();
        static::viewModels();
    }
}
