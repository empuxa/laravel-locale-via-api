<?php

use Empuxa\LocaleViaApi\Controllers\GetLocaleController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    File::deleteDirectory(lang_path('en'));
    File::deleteDirectory(lang_path('vendor/test-plugin'));

    if (! File::exists(lang_path('en'))) {
        File::makeDirectory(lang_path('en'), 0755, true);
        File::makeDirectory(lang_path('vendor/test-plugin/en'), 0755, true);

        File::put(lang_path('en/test.php'), "<?php return ['title' => 'Test'];");
    }
});

it('uses cache for storing locale data', function () {
    $locale = 'en';

    $cacheDriver = config('locale-via-api.cache.driver', 'array');
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

    expect($responseData)->toHaveKeys(['data', 'meta'])
        ->and($responseData['data'])->toBeArray()
        ->and($responseData['data'])->toBe(['test' => ['title' => 'Test']]);

    $expectedHash = md5(json_encode(['test' => ['title' => 'Test']]));
    expect($responseData['meta']['hash'])->toEqual($expectedHash);
});

it('returns a json response with correct structure with vendor', function () {
    File::put(lang_path('vendor/test-plugin/en/vendor-test.php'), "<?php return ['title' => 'Vendor Test'];");

    $controller = new GetLocaleController;
    $response = $controller('en');

    expect($response)->toBeInstanceOf(Illuminate\Http\JsonResponse::class);

    $responseData = $response->getData(true);

    expect($responseData)->toHaveKeys(['data', 'meta'])
        ->and($responseData['data'])->toBeArray()
        ->and($responseData['data'])->toHaveKey('test')
        ->and($responseData['data'])->toHaveKey('vendor-test')
        ->and($responseData['data']['test'])->toBe(['title' => 'Test'])
        ->and($responseData['data']['vendor-test'])->toBe(['title' => 'Vendor Test']);

    $expectedHash = md5(json_encode([
        'test'        => ['title' => 'Test'],
        'vendor-test' => ['title' => 'Vendor Test'],
    ]));

    expect($responseData['meta']['hash'])->toEqual($expectedHash);
});
