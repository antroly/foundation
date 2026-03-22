<?php

declare(strict_types=1);

// Actions must extend the base Action class and be final.
arch('actions extend Action')
    ->expect('App\Actions')
    ->classes()
    ->toExtend('App\Actions\Action')
    ->ignoring('App\Actions\Action');

arch('actions are final')
    ->expect('App\Actions')
    ->classes()
    ->toBeFinal()
    ->ignoring('App\Actions\Action');

arch('actions do not return Eloquent models')
    ->expect('App\Actions')
    ->not->toReturnInstances('Illuminate\Database\Eloquent\Model');

arch('actions do not return paginators')
    ->expect('App\Actions')
    ->not->toReturnInstances('Illuminate\Contracts\Pagination\LengthAwarePaginator');

arch('actions do not return abstract paginators')
    ->expect('App\Actions')
    ->not->toReturnInstances('Illuminate\Pagination\AbstractPaginator');

arch('actions do not depend on Request or FormRequest')
    ->expect('App\Actions')
    ->not->toUse('Illuminate\Http\Request')
    ->not->toUse('Illuminate\Foundation\Http\FormRequest');

arch('actions do not return HTTP responses')
    ->expect('App\Actions')
    ->not->toReturnInstances('Illuminate\Http\JsonResponse')
    ->not->toReturnInstances('Illuminate\Http\Response')
    ->not->toReturnInstances('Symfony\Component\HttpFoundation\Response');

// DTOs are plain data containers — final, no Eloquent.
arch('dtos are final')
    ->expect('App\Dtos')
    ->classes()
    ->toBeFinal();

arch('dtos do not depend on Eloquent')
    ->expect('App\Dtos')
    ->not->toUse('Illuminate\Database\Eloquent\Model');

// Resources extend BaseResource and do not query the database.
arch('resources extend BaseResource')
    ->expect('App\Http\Resources')
    ->classes()
    ->toExtend('App\Http\Resources\BaseResource')
    ->ignoring('App\Http\Resources\BaseResource');

arch('resources do not use Eloquent directly')
    ->expect('App\Http\Resources')
    ->not->toUse('Illuminate\Database\Eloquent\Model')
    ->ignoring('App\Http\Resources\BaseResource');

// ViewModels extend BaseViewModel and do not query the database.
arch('viewmodels extend BaseViewModel')
    ->expect('App\Http\ViewModels')
    ->classes()
    ->toExtend('App\Http\ViewModels\BaseViewModel')
    ->ignoring('App\Http\ViewModels\BaseViewModel');

arch('viewmodels do not use Eloquent directly')
    ->expect('App\Http\ViewModels')
    ->not->toUse('Illuminate\Database\Eloquent\Model')
    ->ignoring('App\Http\ViewModels\BaseViewModel');

// Controllers extend BaseController and do not query the database directly.
arch('controllers extend BaseController')
    ->expect('App\Http\Controllers')
    ->classes()
    ->toExtend('App\Http\Controllers\BaseController')
    ->ignoring('App\Http\Controllers\BaseController');

arch('controllers do not use Eloquent directly')
    ->expect('App\Http\Controllers')
    ->not->toUse('Illuminate\Database\Eloquent\Model')
    ->ignoring('App\Http\Controllers\BaseController');

// Domain exceptions extend DomainException and are final.
arch('domain exceptions extend DomainException')
    ->expect('App\Exceptions')
    ->classes()
    ->toExtend('App\Exceptions\DomainException')
    ->ignoring('App\Exceptions\DomainException')
    ->ignoring('App\Exceptions\AppExceptionHandler');

arch('domain exceptions are final')
    ->expect('App\Exceptions')
    ->classes()
    ->toBeFinal()
    ->ignoring('App\Exceptions\DomainException')
    ->ignoring('App\Exceptions\AppExceptionHandler');
