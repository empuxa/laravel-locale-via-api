<?php

namespace Empuxa\LocaleViaApi\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

class GetLocaleController extends Controller
{
    private const LOCALE_NOT_FOUND = 'Locale not found.';

    /**
     * Handle the incoming request.
     *
     * @throws \Throwable
     */
    public function __invoke(string $locale): JsonResponse
    {
        $this->ensureLocaleIsValid($locale);
        $this->ensureLocaleExists($locale);

        $data = Cache::driver(config('locale-via-api.cache.driver', 'array'))->remember(
            $this->getCacheKey($locale),
            config('locale-via-api.cache.duration', 3600),
            function () use ($locale) {
                return $this->getMergedLocaleData($locale);
            }
        );

        return $this->createJsonResponse($data);
    }

    /**
     * Ensure the locale is valid.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    private function ensureLocaleIsValid(string $locale): void
    {
        abort_unless(in_array($locale, config('locale-via-api.locales'), true), 404, self::LOCALE_NOT_FOUND);
    }

    /**
     * Ensure the locale directory exists.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    private function ensureLocaleExists(string $locale): void
    {
        abort_unless(File::exists(lang_path($locale)), 404, self::LOCALE_NOT_FOUND);
    }

    /**
     * Get the cache key for the given locale.
     */
    private function getCacheKey(string $locale): string
    {
        return config('locale-via-api.cache.prefix', 'locale-via-api:') . $locale;
    }

    /**
     * Get merged locale data.
     */
    private function getMergedLocaleData(string $locale): array
    {
        $data = $this->getLocaleData($locale);

        // Get vendor directories
        $vendorLocales = File::directories(lang_path('vendor'));

        foreach ($vendorLocales as $vendorLocale) {
            $vendorName = basename($vendorLocale);
            $data = array_merge_recursive(
                $data,
                $this->getLocaleData(sprintf('vendor/%s/%s', $vendorName, $locale))
            );
        }

        ksort($data);

        return $data;
    }

    /**
     * Get locale data from files.
     */
    protected function getLocaleData(string $locale): array
    {
        $data = [];
        $directory = lang_path($locale);

        if (! File::exists($directory)) {
            return $data;
        }

        $files = File::allFiles($directory);

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $fileName = $this->getLocaleFileName($file, $locale);
            $data[$fileName] = File::getRequire($file);
        }

        return $data;
    }

    /**
     * Get the locale file name.
     */
    private function getLocaleFileName(SplFileInfo $file, string $locale): string
    {
        $relativePath = $file->getRelativePath();
        $fileName = Str::before($file->getFilename(), '.');

        if (! Str::is($locale, $relativePath)) {
            $fileName = Str::replace('/', '.', Str::before($file->getRelativePathname(), '.'));
            $fileName = Str::replace($locale . '.', '', $fileName);
        }

        return $fileName;
    }

    /**
     * Create a JSON response.
     */
    private function createJsonResponse(array $data): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'meta' => [
                'hash' => md5(json_encode($data)),
            ],
        ]);
    }
}
