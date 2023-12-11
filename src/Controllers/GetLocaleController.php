<?php

namespace Empuxa\LocaleViaApi\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GetLocaleController extends Controller
{
    /**
     * @throws \Throwable
     */
    public function __invoke(string $locale): JsonResponse
    {
        // Doesn't match our whitelist
        abort_unless(in_array($locale, config('locale-via-api.locales'), true), 404);

        // Might be on whitelist, but doesn't exist
        abort_unless(File::exists(lang_path($locale)), 404);

        $data = Cache::driver(config('locale-via-api.cache.driver', 'sync'))->remember(
            config('locale-via-api.cache.prefix', 'locale-via-api:') . $locale,
            config('locale-via-api.cache.duration', 3600),
            function () use ($locale) {
                return $this->getLocaleData($locale);
            });

        return response()->json([
            'data' => $data,
            'meta' => [
                'hash' => md5(json_encode($data)),
            ],
        ]);
    }

    protected function getLocaleData(string $locale): array
    {
        $data = [];
        $files = File::allFiles(lang_path($locale));

        foreach ($files as $file) {
            $fileName = Str::before($file->getFilename(), '.');

            // No support for JSON files right now
            if ($file->getExtension() !== 'php') {
                continue;
            }

            // This is a directory
            if (! Str::is($locale, $file->getRelativePath())) {
                $fileName = Str::replace('/', '.', Str::before($file->getRelativePathname(), '.'));
                $fileName = Str::replace($locale . '.', '', $fileName);
            }

            $data[$fileName] = File::getRequire($file);
        }

        return $data;
    }
}
