<?php

declare(strict_types=1);

// ─── Actions ─────────────────────────────────────────────────────────────────

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

arch('actions do not depend on Request or FormRequest')
    ->expect('App\Actions')
    ->not->toUse('Illuminate\Http\Request')
    ->not->toUse('Illuminate\Foundation\Http\FormRequest');

arch('actions do not return HTTP responses')
    ->expect('App\Actions')
    ->not->toReturnInstances('Illuminate\Http\JsonResponse')
    ->not->toReturnInstances('Illuminate\Http\Response')
    ->not->toReturnInstances('Symfony\Component\HttpFoundation\Response')
    ->not->toReturnInstances('Illuminate\Contracts\Support\Responsable');

arch('actions do not return Eloquent models or collections')
    ->expect('App\Actions')
    ->not->toReturnInstances('Illuminate\Database\Eloquent\Model')
    ->not->toReturnInstances('Illuminate\Database\Eloquent\Collection');

arch('actions do not return paginators')
    ->expect('App\Actions')
    ->not->toReturnInstances('Illuminate\Contracts\Pagination\LengthAwarePaginator')
    ->not->toReturnInstances('Illuminate\Pagination\AbstractPaginator');

// ─── DTOs ─────────────────────────────────────────────────────────────────────

arch('dtos extend Dto')
    ->expect('App\Dtos')
    ->classes()
    ->toExtend('App\Dtos\Dto')
    ->ignoring('App\Dtos\Dto')
    ->ignoring('App\Dtos\Common\CollectionResult')
    ->ignoring('App\Dtos\Common\PaginatedResult');

arch('dtos are final')
    ->expect('App\Dtos')
    ->classes()
    ->toBeFinal()
    ->ignoring('App\Dtos\Dto');

arch('dtos do not depend on Eloquent')
    ->expect('App\Dtos')
    ->not->toUse('Illuminate\Database\Eloquent\Model');

arch('dtos do not depend on Request or FormRequest')
    ->expect('App\Dtos')
    ->not->toUse('Illuminate\Http\Request')
    ->not->toUse('Illuminate\Foundation\Http\FormRequest');

// ─── Requests ────────────────────────────────────────────────────────────────

arch('requests extend ActionRequest')
    ->expect('App\Http\Requests')
    ->classes()
    ->toExtend('App\Http\Requests\ActionRequest')
    ->ignoring('App\Http\Requests\ActionRequest');

arch('requests are final')
    ->expect('App\Http\Requests')
    ->classes()
    ->toBeFinal()
    ->ignoring('App\Http\Requests\ActionRequest');

// ─── Resources ───────────────────────────────────────────────────────────────

arch('resources extend BaseResource')
    ->expect('App\Http\Resources')
    ->classes()
    ->toExtend('App\Http\Resources\BaseResource')
    ->ignoring('App\Http\Resources\BaseResource');

arch('resources are final')
    ->expect('App\Http\Resources')
    ->classes()
    ->toBeFinal()
    ->ignoring('App\Http\Resources\BaseResource');

arch('resources do not use Eloquent directly')
    ->expect('App\Http\Resources')
    ->not->toUse('Illuminate\Database\Eloquent\Model')
    ->ignoring('App\Http\Resources\BaseResource');

// ─── ViewModels ───────────────────────────────────────────────────────────────

arch('viewmodels extend BaseViewModel')
    ->expect('App\Http\ViewModels')
    ->classes()
    ->toExtend('App\Http\ViewModels\BaseViewModel')
    ->ignoring('App\Http\ViewModels\BaseViewModel');

arch('viewmodels are final')
    ->expect('App\Http\ViewModels')
    ->classes()
    ->toBeFinal()
    ->ignoring('App\Http\ViewModels\BaseViewModel');

arch('viewmodels do not use Eloquent directly')
    ->expect('App\Http\ViewModels')
    ->not->toUse('Illuminate\Database\Eloquent\Model')
    ->ignoring('App\Http\ViewModels\BaseViewModel');

// ─── Controllers ─────────────────────────────────────────────────────────────

arch('controllers extend BaseController')
    ->expect('App\Http\Controllers')
    ->classes()
    ->toExtend('App\Http\Controllers\BaseController')
    ->ignoring('App\Http\Controllers\BaseController');

arch('controllers are final')
    ->expect('App\Http\Controllers')
    ->classes()
    ->toBeFinal()
    ->ignoring('App\Http\Controllers\BaseController');

arch('controllers do not use Eloquent directly')
    ->expect('App\Http\Controllers')
    ->not->toUse('Illuminate\Database\Eloquent\Model')
    ->ignoring('App\Http\Controllers\BaseController');

// ─── Domain Exceptions ───────────────────────────────────────────────────────

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
