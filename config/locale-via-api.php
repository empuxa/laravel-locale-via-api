<?php

return [
    /**
     * We cache the locale for a certain amount of time.
     * To disable caching, set driver to 'array'.
     */
    'cache'             => [
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
     * Load vendor files.
     * If set to true, the package will load the vendor files.
     * If set to false, you have to load the vendor files yourself.
     * DEFAULT: true
     */
    'load_vendor_files' => true,

    /**
     * Vendor safelist.
     * If set to an array, only the listed vendor packages will be loaded.
     * If set to null, all vendor packages will be loaded (when load_vendor_files is true).
     * DEFAULT: null (load all)
     */
    'vendor_safelist'   => null,

    /**
     * Should the output be flattened?
     * This will return keys as "api.error.401" instead of "api => error => 401".
     * DEFAULT: false
     */
    'flatten'           => false,

    /**
     * Add your supported locales here.
     */
    'locales'           => [
        'en',
        'de',
    ],
];
