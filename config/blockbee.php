<?php

/**
 * CryptoApiProcessor
 *
 * @author  Aleksandar Rancic <aleks.rancic@gmail.com>
 * @license MIT License
 * @link    https://github.com/spezia/crypto-api-processor
 */

return [

    'api_key' => env('BLOCKBEE_API_KEY'),

    'base_url' => 'https://api.blockbee.io',

    'statuses' => [
        'success' => 'success',
        'created' => 'created',
        'processing' => 'processing',
        'done' => 'done',
        'error' => 'error',
    ],

    'supported_fiat_currencies' => ['AED', 'AUD', 'BGN', 'BRL', 'CAD', 'CHF', 'CNY', 'COP', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'IDR', 'INR', 'JPY', 'LKR', 'MXN', 'MYR', 'NGN', 'NOK', 'PHP', 'PLN', 'RON', 'RUB', 'SEK', 'SGD', 'THB', 'TRY', 'TWD', 'UAH', 'UGX', 'USD', 'ZAR'],

    /*
     * @link https://blockbee.io/cryptocurrencies
     */
    'native_coins' => [
        'arbitrum' => 'arbitrum/eth',
        'avax-c'   => 'avax-c/avax',
        'base'     => 'base/eth',
        'bep20'    => 'bep20/bnb',
        'bera'     => 'bera/bera',
        'erc20'    => 'eth',
        'linea'    => 'linea/eth',
        'monad'    => 'monad/mon',
        'optimism' => 'optimism/eth',
        'polygon'  => 'polygon/pol',
        'sol'      => 'sol/sol',
        'trc20'    => 'trx',
    ],

];
