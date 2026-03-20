<?php

declare(strict_types=1);

use Antroly\Foundation\Http\ResponseMacros;

beforeEach(fn () => ResponseMacros::register());

describe('response()->success()', function () {

    it('returns correct status code', function () {
        expect(response()->success(200, ['id' => 1])->getStatusCode())->toBe(200);
    });

    it('returns correct envelope structure', function () {
        $json = response()->success(200, ['id' => 1])->getData(true);

        expect($json)->toMatchArray([
            'statusCode' => 200,
            'error'      => false,
            'errorCode'  => null,
            'errorBags'  => null,
            'data'       => ['id' => 1],
        ]);
    });

    it('includes message when provided', function () {
        $json = response()->success(201, null, 'Created successfully')->getData(true);

        expect($json['message'])->toBe('Created successfully');
    });

    it('sets message to null when not provided', function () {
        $json = response()->success(200, ['id' => 1])->getData(true);

        expect($json['message'])->toBeNull();
    });

    it('sets data to null when not provided', function () {
        $json = response()->success(204)->getData(true);

        expect($json['data'])->toBeNull();
    });

});

describe('response()->error()', function () {

    it('returns correct status code', function () {
        expect(response()->error(422, 'Validation failed')->getStatusCode())->toBe(422);
    });

    it('returns correct envelope structure', function () {
        $json = response()->error(422, 'Validation failed', ['field' => ['required' => 'Required']], 'validation.failed')->getData(true);

        expect($json)->toMatchArray([
            'statusCode' => 422,
            'error'      => true,
            'message'    => 'Validation failed',
            'errorCode'  => 'validation.failed',
            'data'       => null,
        ]);
    });

    it('sets data to null always', function () {
        $json = response()->error(500, 'Server error')->getData(true);

        expect($json['data'])->toBeNull();
    });

});

describe('response()->error() fallback error codes', function () {

    it('uses validation.failed for 422', function () {
        expect(response()->error(422)->getData(true)['errorCode'])->toBe('validation.failed');
    });

    it('uses http.401 for 401', function () {
        expect(response()->error(401)->getData(true)['errorCode'])->toBe('http.401');
    });

    it('uses http.403 for 403', function () {
        expect(response()->error(403)->getData(true)['errorCode'])->toBe('http.403');
    });

    it('uses http.404 for 404', function () {
        expect(response()->error(404)->getData(true)['errorCode'])->toBe('http.404');
    });

    it('uses http.405 for 405', function () {
        expect(response()->error(405)->getData(true)['errorCode'])->toBe('http.405');
    });

    it('uses http.429 for 429', function () {
        expect(response()->error(429)->getData(true)['errorCode'])->toBe('http.429');
    });

    it('uses exception.unexpected for 500', function () {
        expect(response()->error(500)->getData(true)['errorCode'])->toBe('exception.unexpected');
    });

    it('uses exception.unexpected for any 5xx', function () {
        expect(response()->error(503)->getData(true)['errorCode'])->toBe('exception.unexpected');
    });

    it('uses dynamic http.{code} for other codes', function () {
        expect(response()->error(409)->getData(true)['errorCode'])->toBe('http.409');
    });

    it('uses provided errorCode over fallback', function () {
        expect(response()->error(422, 'Error', [], 'custom.code')->getData(true)['errorCode'])->toBe('custom.code');
    });

});
