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

];
