<?php

namespace App\ExchangeRate;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Cache {
    private $dbtable = 'cache_exchange_rates';

    /**
     * The cache duration (in seconds)
     *
     * Default: 2 hours
     */
    public $expire = 7200;

    private $nextAutoclearFile = 'next_autoclear';

    public function __construct($app)
    {
        $this->setExpire(
            $app->config->get('exchangerate.cache.expire', $this->expire)
        );

        $this->runAutoclear(
            $app->config->get('exchangerate.cache.auto_clear', 'tomorrow')
        );
    }

    /**
     * Sets the expiration of cached results
     *
     * @return void
     */
    public function setExpire($expire)
    {
        $this->expire = $expire;
    }

    public function get($from, $to)
    {
        $cachedItem = DB::select(
            "SELECT `id`, `currency_from`, `currency_to`, `rate`, `expires_at` FROM `{$this->dbtable}`
                WHERE `currency_from` IN(?, ?) AND `currency_to` IN (?, ?)",
            [ $from, $to, $from, $to ]
        );

        if (empty($cachedItem)) {
            return null;
        }

        $result = get_object_vars($cachedItem[0]);

        if ($this->isExpired($result['expires_at'])) {

            DB::delete("DELETE FROM `{$this->dbtable}` WHERE id = ?", [ $result['id'] ]);
            return null;
        }

        $normalizedResult = $this->createNormalizedResult([
            'from_cache' => true
        ]);

        /**
         * This is for:
         *
         * `
         * If possible, if you have already cached say EUR > USD,
         * then you should not cache USD > EUR but re-use the EUR>USD value in reverse (1/x)
         * `
         *
         * Determine if it needs to apply inverse by checking if $from is not the same with `currency_from`
         */
        if ($from != $result['currency_from']) {
            $normalizedResult['currency_from'] = $result['currency_to'];
            $normalizedResult['currency_to'] = $result['currency_from'];
            $normalizedResult['rate'] = 1 / $result['rate'];
        }


        return array_merge($result, array_filter($normalizedResult));
    }

    public function add($from, $to, $rate)
    {
        $result = $this->createNormalizedResult([
            'currency_from' => $from,
            'currency_to'   => $to,
            'rate' => $rate,
            'expires_at'    => date('Y-m-d H:i:s', strtotime('+'. $this->expire .' sec'))
        ]);

        DB::insert(
            "INSERT INTO `{$this->dbtable}` (`currency_from`, `currency_to`, `rate`, `expires_at`) VALUES (:from, :to, :rate, :expire)",
            array_values($result)
        );

        $result['from_cache'] = false;

        return $result;
    }

    public function isExpired($datetime)
    {
        $now = new \DateTime();
        $expiration = new \DateTime($datetime);

        return ($now->getTimestamp() - $expiration->getTimestamp()) > $this->expire;
    }

    public function clearAll()
    {
        DB::statement("TRUNCATE TABLE `{$this->dbtable}`");
    }

    /**
     * This is for:
     *
     * `
     * The cache should clear itself automatically,
     * periodically, of expired exchange rate pairs
     * (no need for a separate program/process/cronjob)
     * `
     *
     * $when is relative to current date (as-in now) and should follow
     * this formatting: https://www.php.net/manual/en/datetime.formats.relative.php
     *
     * @param $when When the autoclear should run. Default, the next day
     * @return void
     */
    public function runAutoclear($when = 'tomorrow')
    {
        if (!Storage::exists($this->nextAutoclearFile)) {
            $this->setNextAutoclear($when);
        }

        $nextAutoclear = \DateTime::createFromFormat('Y-m-d H:i:s', trim(Storage::get($this->nextAutoclearFile)));
        $now = new \DateTime();

        if ($now->getTimestamp() > $nextAutoclear->getTimestamp()) {
            $this->clearAll();

            $this->setNextAutoclear($when);
        }
    }

    private function setNextAutoclear($annotation)
    {
        $now = new \DateTime();
        $now->modify($annotation);

        Storage::put($this->nextAutoclearFile, $now->format('Y-m-d H:i:s'));
    }

    /**
     * The purpose of this is to create a standard result set
     * for: get()/add()
     *
     * And also for Converter::convert() to create a dummy result set if
     * $from == $to is equal.
     *
     * @params $params The result set
     */
    public function createNormalizedResult($result = [])
    {
        $defaults = [
            'currency_from' => null,
            'currency_to'   => null,
            'rate'          => null,
            'expires_at'    => null
        ];

        return array_merge($defaults, $result);
    }
}
