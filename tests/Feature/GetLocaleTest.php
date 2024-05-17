<?php

use Empuxa\LocaleViaApi\Controllers\GetLocaleController;
use Illuminate\Http\Request;
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
    $request = new Request;
    $controller($request, $locale);
});

it('returns a json response with correct structure', function () {
    $controller = new GetLocaleController;
    $request = new Request;
    $response = $controller($request, 'en');

    expect($response)->toBeInstanceOf(Illuminate\Http\JsonResponse::class);

    $responseData = $response->getData(true);

    expect($responseData)->toHaveKeys(['data', 'meta'])
        ->and($responseData['data'])->toBeArray()
        ->and($responseData['data'])->toBe(['test' => ['title' => 'Test']]);

    $expectedHash = md5(json_encode(['test' => ['title' => 'Test']]));

    expect($responseData['meta']['hash'])->toEqual($expectedHash);
});

it('returns a json response with correct structure with vendor', function () {
    config(['locale-via-api.load_vendor_files' => true]);

    File::put(lang_path('vendor/test-plugin/en/vendor-test.php'), "<?php return ['title' => 'Vendor Test'];");

    $controller = new GetLocaleController;
    $request = new Request;
    $response = $controller($request, 'en');

    expect($response)->toBeInstanceOf(Illuminate\Http\JsonResponse::class);

    $responseData = $response->getData(true);

    expect($responseData)->toHaveKeys(['data', 'meta'])
        ->and($responseData['data'])->toBeArray()
        ->and($responseData['data'])->toHaveKey('test')
        ->and($responseData['data'])->toHaveKey('vendor.test-plugin.vendor-test')
        ->and($responseData['data']['test'])->toBe(['title' => 'Test'])
        ->and($responseData['data']['vendor.test-plugin.vendor-test'])->toBe(['title' => 'Vendor Test']);

    $expectedHash = md5(json_encode([
        'test'                           => ['title' => 'Test'],
        'vendor.test-plugin.vendor-test' => ['title' => 'Vendor Test'],
    ]));

    expect($responseData['meta']['hash'])->toEqual($expectedHash);
});

it('returns a json response without vendor files when disabled', function () {
    config(['locale-via-api.load_vendor_files' => false]);

    File::put(lang_path('vendor/test-plugin/en/vendor-test.php'), "<?php return ['title' => 'Vendor Test'];");

    $controller = new GetLocaleController;
    $request = new Request;
    $response = $controller($request, 'en');

    expect($response)->toBeInstanceOf(Illuminate\Http\JsonResponse::class);

    $responseData = $response->getData(true);

    expect($responseData)->toHaveKeys(['data', 'meta'])
        ->and($responseData['data'])->toBeArray()
        ->and($responseData['data'])->toHaveKey('test')
        ->and($responseData['data'])->not->toHaveKey('vendor.test-plugin.vendor-test')
        ->and($responseData['data']['test'])->toBe(['title' => 'Test']);

    $expectedHash = md5(json_encode([
        'test' => ['title' => 'Test'],
    ]));

    expect($responseData['meta']['hash'])->toEqual($expectedHash);
});

it('returns a flattened json response with correct structure', function () {
    File::put(lang_path('en/test.php'), "<?php return ['api' => ['error' => ['401' => 'Unauthenticated.', '403' => 'Forbidden.', '404' => 'Not Found.', '422' => 'Unprocessable Entity.']]];");

    $request = new Request(['flatten' => true]);

    $controller = new GetLocaleController;
    $response = $controller($request, 'en');

    expect($response)->toBeInstanceOf(Illuminate\Http\JsonResponse::class);

    $responseData = $response->getData(true);

    expect($responseData)->toHaveKeys(['data', 'meta'])
        ->and($responseData['data'])->toBeArray()
        ->and($responseData['data'])->toBe([
            'test.api.error.401' => 'Unauthenticated.',
            'test.api.error.403' => 'Forbidden.',
            'test.api.error.404' => 'Not Found.',
            'test.api.error.422' => 'Unprocessable Entity.',
        ]);

    $expectedHash = md5(json_encode([
        'test.api.error.401' => 'Unauthenticated.',
        'test.api.error.403' => 'Forbidden.',
        'test.api.error.404' => 'Not Found.',
        'test.api.error.422' => 'Unprocessable Entity.',
    ]));

    expect($responseData['meta']['hash'])->toEqual($expectedHash);
});

it('returns a flattened json response with correct structure with vendor', function () {
    config(['locale-via-api.load_vendor_files' => true]);

    File::put(lang_path('en/test.php'), "<?php return ['api' => ['error' => ['401' => 'Unauthenticated.', '403' => 'Forbidden.', '404' => 'Not Found.', '422' => 'Unprocessable Entity.']]];");
    File::put(lang_path('vendor/test-plugin/en/vendor-test.php'), "<?php return ['title' => 'Vendor Test'];");

    $request = new Request(['flatten' => true]);

    $controller = new GetLocaleController;
    $response = $controller($request, 'en');

    expect($response)->toBeInstanceOf(Illuminate\Http\JsonResponse::class);

    $responseData = $response->getData(true);

    expect($responseData)->toHaveKeys(['data', 'meta'])
        ->and($responseData['data'])->toBeArray()
        ->and($responseData['data'])->toHaveKey('test.api.error.401')
        ->and($responseData['data'])->toHaveKey('vendor.test-plugin.vendor-test.title')
        ->and($responseData['data']['test.api.error.401'])->toBe('Unauthenticated.')
        ->and($responseData['data']['vendor.test-plugin.vendor-test.title'])->toBe('Vendor Test');

    $expectedHash = md5(json_encode([
        'test.api.error.401'                   => 'Unauthenticated.',
        'test.api.error.403'                   => 'Forbidden.',
        'test.api.error.404'                   => 'Not Found.',
        'test.api.error.422'                   => 'Unprocessable Entity.',
        'vendor.test-plugin.vendor-test.title' => 'Vendor Test',
    ]));

    expect($responseData['meta']['hash'])->toEqual($expectedHash);
});
