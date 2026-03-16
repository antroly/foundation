<?php

declare(strict_types=1);

describe('Architecture', function () {

    it('actions extend base Action class')
        ->expect('App\Actions')
        ->toExtend('App\Actions\Action');

    it('actions do not return Eloquent models')
        ->expect('App\Actions')
        ->not->toUse('Illuminate\Database\Eloquent\Model');

    it('dtos extend BaseDto')
        ->expect('App\Dtos')
        ->toExtend('App\Dtos\BaseDto');

    it('dtos do not depend on Eloquent models')
        ->expect('App\Dtos')
        ->not->toUse('Illuminate\Database\Eloquent\Model');

    it('resources extend BaseResource')
        ->expect('App\Http\Resources')
        ->toExtend('App\Http\Resources\BaseResource');

    it('resources do not depend on Eloquent models')
        ->expect('App\Http\Resources')
        ->not->toUse('Illuminate\Database\Eloquent\Model');

    it('controllers do not use Eloquent directly')
        ->expect('App\Http\Controllers')
        ->not->toUse('Illuminate\Database\Eloquent\Model');

    it('viewmodels extend BaseViewModel')
        ->expect('App\Http\ViewModels')
        ->toExtend('App\Http\ViewModels\BaseViewModel');

});
