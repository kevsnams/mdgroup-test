<?php
namespace App\ExchangeRate\Facades;

use Illuminate\Support\Facades\Facade;

class ExchangeRate extends Facade {
    protected static function getFacadeAccessor()
    {
        return 'exchange_rate';
    }
}
