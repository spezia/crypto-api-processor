<?php

/**
 * CryptoApiProcessor
 *
 * @author  Aleksandar Rancic <aleks.rancic@gmail.com>
 * @license https://github.com/spezia/crypto-api-processor/blob/master/LICENSE (MIT License)
 * @version 1.0.0
 * @link    https://github.com/spezia/crypto-api-processor
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spezia\CryptoApiProcessor;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Spezia\CryptoApiProcessor\Exceptions\CryptoApiProcessorException;
use Spezia\CryptoApiProcessor\Helpers\CryptoApiAdapterHelper;

/**
 * @link https://docs.blockbee.io/
 * @link https://blockbee.io/cryptocurrencies [ find Ticker ]
 */
class CryptoApiAdapter
{
    use CryptoApiAdapterHelper;

    public function getBaseUrl(): string
    {
        return config('blockbee.base_url');
    }

    protected function getApiKey(): string
    {
        return config('blockbee.api_key');
    }

    public function get(#[\SensitiveParameter] string $url): iterable
    {
        $response = Http::get($url);

        if (200 != $response->status()) {
            Log::error('BlockBee response: ',  $response->json());
            throw new CryptoApiProcessorException('Error fetching info.');
        }

        return $response->json();
    }

    public function post(#[\SensitiveParameter] string $url, #[\SensitiveParameter] array|string $params, string $contentType = 'application/json'): iterable
    {
        $request = Http::withHeaders([
            'Content-Type' => $contentType,
        ]);

        $response = is_array($params) ? $request->post($url, $params) : $request->withBody($params, $contentType)->post($url);

        if (200 != $response->status()) {
            Log::error('BlockBee response has status ' . $response->status());
            throw new CryptoApiProcessorException('Your request could not be processed, please try again later');
        }

        return $response->json();
    }

    /**
     * @link https://docs.blockbee.io/#operation/blockbeeinfo
     */
    public function getInfo(int $hasPrices = 1): iterable
    {
        if (!in_array($hasPrices, [0, 1], true)) {
            throw new CryptoApiProcessorException('Prices must be either 0 or 1.');
        }

        $query = ['prices' => $hasPrices];

        return $this->get($this->getBaseUrl() . '/info/?' . http_build_query($query));
    }

    /**
     * @link https://docs.blockbee.io/#operation/info
     */
    public function getInfoByTicker(string $ticker, int $hasPrices = 1): iterable
    {
        if (!in_array($hasPrices, [0, 1], true)) {
            throw new CryptoApiProcessorException('Prices must be either 0 or 1.');
        }

        $query = [
            'apikey' => $this->getApiKey(),
            'prices' => $hasPrices
        ];

        return $this->get($this->getBaseUrl() . '/' . strtolower($ticker) . '/info/?' . http_build_query($query));
    }

    /**
     * @link https://docs.blockbee.io/#operation/qrcode
     */
    public function getQRCode(string $ticker, string $address, float $amount, int $sizeInPx = 512): iterable
    {
        $query = [
            'apikey' => $this->getApiKey(),
            'address' => $address,
            'value' => $amount,
            'size' => $sizeInPx
        ];

        return $this->get($this->getBaseUrl() . '/' . strtolower($ticker) . '/qrcode/?' . http_build_query($query));
    }

    /**
     * @link https://docs.blockbee.io/#operation/create
     */
    public function getNewAddress(string $ticker, string $callback, iterable $params = []): iterable
    {
        $query = [
            'apikey' => $this->getApiKey(),
            'callback' => $callback,
            'address' => $params['address'] ?? '',
            'pending' => $params['pending'] ?? 0,
            'confirmations' => $params['confirmations'] ?? 1,
            'post' => $params['post'] ?? 1,
            'json' => $params['json'] ?? 1,
            'priority' => $params['priority'] ?? 'default',
            'multi_token' => $params['multi_token'] ?? 0,
            'convert' => $params['convert'] ?? 0,
        ];

        return $this->get($this->getBaseUrl() . '/' . strtolower($ticker) . '/create/?' . http_build_query($query));
    }

    /**
     * @link https://docs.blockbee.io/#operation/payoutstatus
     */
    public function statusPayout(string $payoutId): iterable
    {
        $query = [
            'apikey' => $this->getApiKey(),
        ];

        $payload = "payout_id=$payoutId";

        return $this->post(
            $this->getBaseUrl() . '/payout/status/?' . http_build_query($query),
            contentType: 'application/x-www-form-urlencoded',
            params: $payload
        );
    }

    /**
     * @link https://docs.blockbee.io/#operation/payoutrequestbulk
     */
    public function processPayout(string $ticker, string $address, float $amount): iterable
    {
        $query = [
            'apikey' => $this->getApiKey()
        ];

        $payload = [
            'outputs' => [
                $address => $amount
            ]
        ];

        return $this->post($this->getBaseUrl() . '/' . strtolower($ticker) . '/payout/request/bulk/process/?' . http_build_query($query), $payload);
    }

    /**
     * @link https://docs.blockbee.io/#operation/payoutbalance
     */
    public function fetchTotalBalance(string $ticker): float
    {
        $query = [
            'apikey' => $this->getApiKey(),
        ];

        $response = $this->get($this->getBaseUrl() . '/' . strtolower($ticker) . '/payout/balance/?' . http_build_query($query));

        return (float) $response['balance'];
    }

    /**
     * @link https://docs.blockbee.io/#operation/estimate
     */
    public function getBlockchainFee(string $ticker, int $addresses = 1, string $priority = 'default'): iterable
    {
        $query = [
            'apikey' => $this->getApiKey(),
            'addresses' => $addresses,
            'priority' => $priority
        ];

        return $this->get($this->getBaseUrl() . '/' . strtolower($ticker) . '/estimate/?' . http_build_query($query));
    }
}
