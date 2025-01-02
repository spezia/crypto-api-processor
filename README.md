# Crypto API Processor

## **Introduction**
This package is an unofficial integration of the [Blockbee](https://blockbee.io/) cryptocurrency payment gateway for [Laravel](https://laravel.com/) applications.

This version of the package does not cover all Blockbee API  endpoints. It supports only the most important features. To explore all API endpoints, refer to the [Blockbee Documentation](https://docs.blockbee.io/).


## Installation

Use Composer to install the package.

```bash
composer require spezia/crypto-api-processor
```

If you don't use auto-discovery, add the **CryptoApiProcessorServiceProvider** to the `providers` list in config/app.php.

```
'providers' => [
    ...
    Spezia\CryptoApiProcessor\Providers\CryptoApiProcessorServiceProvider::class,
],
```

The package includes a configuration file. Publish it with the following command:

```
php artisan vendor:publish --tag=blockbee-config
```


To integrate Blockbee into a Laravel app, you need to open Blockbee account and fetch API key. Add this key to your `.env` file.

```
BLOCKBEE_API_KEY=example
```

## Usage

```php

use Spezia\CryptoApiProcessor\CryptoApiAdapter;

$blockBeeAdapter = new CryptoApiAdapter();

```

You can take advantage of helper methods via the included trait:

```php

use Spezia\CryptoApiProcessor\Helpers\CryptoApiAdapterHelper;

```
Please open both files to see available methods.

## Examples

Here are a couple of examples of how to use the **CryptoApiAdapter**. You can copy this code into your Laravel controller and test it.

```php

<?php

namespace App\Http\Controllers;

use Spezia\CryptoApiProcessor\CryptoApiAdapter;
use Spezia\CryptoApiProcessor\Exceptions\CryptoApiProcessorException;
use Spezia\CryptoApiProcessor\Helpers\CryptoApiAdapterHelper;

class ShowcaseExampleController extends Controller
{
    use CryptoApiAdapterHelper;

    /**
     * Fetch info for LTC currency
     */
    public function info(CryptoApiAdapter $blockBeeAdapter)
    {
        $response = $blockBeeAdapter->getInfoByTicker('LTC');

        return response()->json($response);
    }

    /**
     * Fetch qr_code or payment_uri for payment
     */
    public function payin(CryptoApiAdapter $blockBeeAdapter)
    {
        $ticker   = 'LTC';
        $amount   =  0.5;
        $callback = 'https://example.com/callback/product/123';

        // fetch a new  wallet address instead of the real one, we will get a new unique address for every transaction
        $responseAddress = $blockBeeAdapter->getNewAddress($ticker, $callback);

        if (strtolower($responseAddress['status']) === config('blockbee.statuses.success')) {
            $response = $blockBeeAdapter->getQRCode($ticker, $responseAddress['address_in'], $amount);
        }

        return response()->json($response['payment_uri'] ?? 'Payment uri not found.');
    }

    public function payout(CryptoApiAdapter $blockBeeAdapter)
    {
        $ticker  = 'LTC';
        $address = 'receiver_wallet_address';
        $amount  = 0.001;

        try {
            $response = $blockBeeAdapter->processPayout($ticker, $address, $amount);

            if ($response['status'] === config('blockbee.statuses.success')) {
                while ($response['payout_info']['status'] === config('blockbee.statuses.processing')) {
                    $response = $blockBeeAdapter->statusPayout($response['payout_info']['id']);
                    sleep(30);
                }

                $msg = $response['payout_info']['status'] ===  config('blockbee.statuses.done') ? 'Update transaction status to done.' : 'Payment failed.';
                return response()->json($msg);
            }
        } catch (CryptoApiProcessorException $e) {
            return response()->json($e->getMessage());
        }

        return response()->json('Error.');
    }

    /**
     * Fetch fee for LTC currency for given blockbee account wallet address
     */
    public function fee(CryptoApiAdapter $blockBeeAdapter)
    {
        $ticker   = 'LTC';
        $response = $blockBeeAdapter->getBlockchainFee($ticker);

        return response()->json('Fee is ' . $response['estimated_cost'] ?? 'Not found.');
    }

    /**
     * Fetch fee for LTC currency using CryptoApiAdapterHelper
     */
    public function fiatFee()
    {
        $ticker   = 'LTC';
        $response = $this->estimatedBlockchaiFiatFee($ticker, 'USD');

        return response()->json('USD fee is ' . $response);
    }

    /**
     * Check if the amount has exceeded the balance using CryptoApiAdapterHelper
     */
    public function validateAmount()
    {
        try {
            $ticker = 'LTC';
            $amount = 0.5;
            $fiat   = 'USD';

            $response = $this->hasExceedBalance($amount, $ticker, $fiat);

            return response()->json($response ? 'Amount exceeds balance.' : 'Amount is valid.');
        } catch (CryptoApiProcessorException $e) {
            return response()->json($e->getMessage());
        }
    }
}

```


## License

Project Title is released under the MIT License. See the **[MIT](./LICENSE.md)** file for details.
