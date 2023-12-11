<?php

use Empuxa\LocaleViaApi\Controllers\GetLocaleController;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    File::deleteDirectory(lang_path() . '/en');

    if (! File::exists(lang_path('en'))) {
        File::makeDirectory(lang_path() . '/en', 0755, true);
        File::put(lang_path() . '/en/test.php', "<?php return ['title' => 'Test'];");
    }
});

it('uses cache for storing locale data', function () {
    $locale = 'en';

    $cacheDriver = config('locale-via-api.cache.driver', 'sync');
    $cacheKey = config('locale-via-api.cache.prefix') . $locale;
    $cacheDuration = config('locale-via-api.cache.duration');

    Cache::shouldReceive('driver')
        ->with($cacheDriver)
        ->andReturnSelf();

    Cache::shouldReceive('remember')
        ->with($cacheKey, $cacheDuration, Mockery::any())
        ->once()
        ->andReturn([]);

    $controller = new GetLocaleController;
    $controller($locale);
});

it('returns a json response with correct structure', function () {
    $controller = new GetLocaleController;
    $response = $controller('en');

    expect($response)->toBeInstanceOf(Illuminate\Http\JsonResponse::class);

    $responseData = $response->getData(true);

    expect($responseData)->toHaveKeys(['data', 'meta']);

    expect($responseData['data'])->toBeArray()->toBe(['test' => ['title' => 'Test']]);

    $expectedHash = md5(json_encode(['test' => ['title' => 'Test']]));
    expect($responseData['meta']['hash'])->toEqual($expectedHash);
});
