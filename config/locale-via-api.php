<?php

return [
    /**
     * We cache the locale for a certain amount of time.
     * To disable caching, set driver to 'array'.
     */
    'cache'   => [
        /**
         * The cache driver to use.
         * You can use any driver supported by Laravel.
         * DEFAULT: 'array' (no caching)
         */
        'driver'   => 'array',

        /**
         * The cache duration in seconds.
         * DEFAULT: 60 * 60 (1 hour)
         */
        'duration' => 60 * 60,

        /**
         * The cache prefix.
         * DEFAULT: 'locale-via-api:'
         */
        'prefix'   => 'locale-via-api:',
    ],

    /**
     * Add your supported locales here.
     */
    'locales' => [
        'en',
        'de',
    ],
];
