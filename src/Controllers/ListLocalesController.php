<?php

namespace Empuxa\LocaleViaApi\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class ListLocalesController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'data' => config('locale-via-api.locales'),
            'meta' => [
                'hash' => md5(json_encode(config('locale-via-api.locales'))),
            ],
        ]);
    }
}
