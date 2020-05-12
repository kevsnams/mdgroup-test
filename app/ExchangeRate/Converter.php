<?php

namespace App\ExchangeRate;
use Illuminate\Support\Facades\Http;

class Converter extends Cache {
    private $apiBaseUrl = 'https://api.exchangeratesapi.io/latest';

    public function __construct($app)
    {
        parent::__construct($app);

        $this->apiBaseUrl = $app->config->get('exchangerate.api.base_url', $this->apiBaseUrl);
    }

    public function convert($from, $to)
    {
        /**
         * If $from and $to are equal, then there's no need to go
         * through all the API -> Cache process.
         *
         * Just set the `rate` to 1 and
         * `from_cache` to 0 (because it really didn't came from cache db)
         */
        if ($from == $to) {
            return $this->createNormalizedResult([
                'currency_from' => $from,
                'currency_to' => $to,
                'rate' => 1,
                'from_cache' => 0
            ]);
        }

        $cachedItem = $this->get($from, $to);

        if ($cachedItem === null) {
            $result = Http::get(
                $this->apiBaseUrl . '?' . http_build_query([
                    'base' => $from, 'symbols' => $to
                ])
            );

            $cachedItem = $this->add($from, $to, $result['rates'][$to]);
        }

        return $cachedItem;
    }
}
