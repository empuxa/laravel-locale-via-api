<?php

use Empuxa\LocaleViaApi\Controllers\ListLocalesController;

it('returns a json response with correct structure and data from ListLocalesController', function () {
    $mockedLocales = ['en', 'de'];

    $controller = new ListLocalesController;

    $response = $controller();

    expect($response)->toBeInstanceOf(Illuminate\Http\JsonResponse::class);

    $responseData = $response->getData(true);

    expect($responseData)->toHaveKeys(['data', 'meta']);

    expect($responseData['data'])->toBeArray()->toBe($mockedLocales);

    expect($responseData['meta']['hash'])->toEqual(md5(json_encode($mockedLocales)));
});
