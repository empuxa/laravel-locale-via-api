<?php

namespace Empuxa\LocaleViaApi\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

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
        // Ensure locale is valid and exists
        $this->ensureLocaleIsValid($locale);
        $this->ensureLocaleExists($locale);

        // Get cached data or generate it if not present
        $data = Cache::driver(config('locale-via-api.cache.driver', 'array'))->remember(
            $this->getCacheKey($locale),
            config('locale-via-api.cache.duration', 3600),
            function () use ($locale) {
                return $this->getMergedLocaleData($locale);
            }
        );

        // Return JSON response
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
        return sprintf('%s%s', config('locale-via-api.cache.prefix', 'locale-via-api:'), $locale);
    }

    /**
     * Get merged locale data.
     */
    private function getMergedLocaleData(string $locale): array
    {
        $data = $this->getLocaleData($locale);

        if (config('locale-via-api.load_vendor_files', true)) {
            // Get vendor directories
            $vendorLocales = File::directories(lang_path('vendor'));

            foreach ($vendorLocales as $vendorLocale) {
                $vendorName = basename($vendorLocale);
                $data = array_merge_recursive(
                    $data,
                    $this->getVendorLocaleData(sprintf('vendor/%s/%s', $vendorName, $locale), $vendorName)
                );
            }
        }

        ksort($data);

        return $data;
    }

    /**
     * Get locale data from files.
     */
    protected function getLocaleData(string $locale): array
    {
        return $this->loadLocaleFiles(lang_path($locale));
    }

    /**
     * Get vendor locale data from files.
     */
    protected function getVendorLocaleData(string $path, string $vendorName): array
    {
        return $this->loadLocaleFiles(lang_path($path), sprintf('vendor.%s', $vendorName));
    }

    /**
     * Load locale files from a given path.
     */
    protected function loadLocaleFiles(string $directory, string $prefix = ''): array
    {
        $data = [];

        if (! File::exists($directory)) {
            return $data;
        }

        $files = File::allFiles($directory);

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $relativePath = Str::replaceFirst($directory . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $fileName = Str::before($relativePath, '.');

            // Convert the relative path to a dot notation key
            $key = Str::replace(DIRECTORY_SEPARATOR, '.', $fileName);

            if ($prefix) {
                $key = sprintf('%s.%s', $prefix, $key);
            }

            $data[$key] = File::getRequire($file);
        }

        return $data;
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
