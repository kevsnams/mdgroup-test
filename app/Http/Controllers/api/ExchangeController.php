<?php

namespace App\Http\Controllers\api;

use App\ExchangeRate\Facades\ExchangeRate;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ExchangeController extends Controller
{
    public function convert($amount, $from, $to)
    {
        $allowedCurrencies = [ 'CAD', 'JPY', 'USD', 'GBP', 'EUR', 'RUB', 'HKD', 'CHF' ];
        $allowedCurrenciesIn = implode(',', $allowedCurrencies);

        $validator = Validator::make(compact('amount', 'from', 'to'), [
            'from' => [
                'required',
                'string',
                'in:'. $allowedCurrenciesIn
            ],

            'to' => [
                'required',
                'string',
                'in:'. $allowedCurrenciesIn
            ],

            'amount' => [
                'required',
                'numeric'
            ]
        ]);

        if ($validator->fails()) {

            /**
             * According to the specs given, only $from and $to currency codes
             * have a specific error messages. Incorrect $amount will be
             * considered as an 'invalid request'
             */

            $errors = $validator->errors();

            if ($errors->has('from')) {
                return response(json_encode([
                    'error' => 1,
                    'message' => "currency code {$from} not supported"
                ]));
            }

            if ($errors->has('to')) {
                return response(json_encode([
                    'error' => 1,
                    'message' => "currency code {$to} not supported"
                ]));
            }

            return response(json_encode([
                'error' => 1,
                'msg' => 'invalid request'
            ]));
        }

        $exchangeRate = ExchangeRate::convert($from, $to);
        $convertedAmount = round($exchangeRate['rate'] * $amount, 2);

        return response(json_encode([
            'error' => 0,
            'amount' => $convertedAmount,

            /**
             * According to the specs give, 'fromCache' can
             * be either 1 or 0.. and 'from_cache' returns true/false.
             * So, type casting...
             */
            'fromCache' => (int) $exchangeRate['from_cache']
        ]));
    }

    public function cacheClear()
    {
        ExchangeRate::clearAll();

        return response(json_encode([
            'error' => 0,
            'msg' => 'OK'
        ]));
    }

    public function info()
    {
        return response(json_encode([
            'error' => 0,
            'msg' => 'API written by Kevin Namuag'
        ]));
    }

    public function fallback()
    {
        return response(json_encode([
            'error' => 1,
            'msg' => 'invalid request'
        ]));
    }
}
