<?php

return [
    'api' => [
        'base_url' => env('EXCHANGE_RATE_API_BASE_URL')
    ],

    'cache' => [
        /**
         * The lifetime of a cached item. Value must be in seconds
         */
        'expire' => env('EXCHANGE_RATE_CACHE_EXPIRE'),

        /**
         * When the cache auto clears (recurring)
         *
         * Refer to: https://www.php.net/manual/en/datetime.formats.relative.php
         * for the values in this config.
         */
        'auto_clear' => env('EXCHANGE_RATE_CACHE_AUTOCLEAR')
    ]
];
