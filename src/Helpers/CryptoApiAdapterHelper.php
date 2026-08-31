<?php

/**
 * CryptoApiProcessor
 *
 * @author  Aleksandar Rancic <aleks.rancic@gmail.com>
 * @license MIT License
 * @link    https://github.com/spezia/crypto-api-processor
 */

declare(strict_types=1);

namespace Spezia\CryptoApiProcessor\Helpers;

use Spezia\CryptoApiProcessor\CryptoApiAdapter;
use Spezia\CryptoApiProcessor\Exceptions\CryptoApiProcessorException;

trait CryptoApiAdapterHelper
{
    private ?CryptoApiAdapter $adapterInstance = null;

    private function getAdapterInstance(): CryptoApiAdapter
    {
        if ($this instanceof CryptoApiAdapter) {
            return $this;
        }

        if ($this->adapterInstance === null) {
            $this->adapterInstance = new CryptoApiAdapter();
        }

        return $this->adapterInstance;
    }

    /**
     * Check if an amount is greater than the balance of the wallet address
     *
     * @param float  $amount
     * @param string $ticker        [ 'BTC', 'LTC', 'TRC20/USDT',... ]
     * @return boolean
     */
    public function hasExceedBalance(float $amount, string $ticker): bool
    {
        $adapter = $this->getAdapterInstance();
        $balance = $adapter->fetchTotalBalance($ticker);
        $info = $adapter->getInfoByTicker($ticker);

        if (!isset($info['fee_percent'])) {
            throw new CryptoApiProcessorException('BlockBee did not return a fee percent for ' . $ticker . '.');
        }

        $required = $amount + ($amount / 100 * (float) $info['fee_percent']);

        if (strtolower($ticker) === $this->nativeCoinFor($ticker)) {
            return $required + $this->estimatedBlockchainCryptoFee($ticker) > $balance;
        }

        return $required > $balance || $this->hasExceedFeeBalance($ticker);
    }

    /**
     * Check if fee is greater than the native coin balance
     *
     * @param string $ticker  [ 'BTC', 'LTC', 'TRC20/USDT',... ]
     * @return bool
     */
    public function hasExceedFeeBalance(string $ticker): bool
    {
        $fee = $this->estimatedBlockchainCryptoFee($ticker);
        $balance = $this->getAdapterInstance()->fetchTotalBalance($this->nativeCoinFor($ticker));

        return $fee > $balance;
    }

    /**
     * Ticker of the wallet [ 'TRC20/USDT' => 'trx', 'BASE/USDC' => 'base/eth' ]
     *
     * @param string $ticker  [ 'BTC', 'LTC', 'TRC20/USDT',... ]
     * @return string
     */
    public function nativeCoinFor(string $ticker): string
    {
        $segments = explode('/', strtolower($ticker));

        if (count($segments) === 1) {
            return $segments[0];
        }

        $prefix = $segments[0];
        $nativeCoins = config('blockbee.native_coins');

        if (!isset($nativeCoins[$prefix])) {
            throw new CryptoApiProcessorException('Unknown chain prefix "' . $prefix . '", add it to the blockbee.native_coins config.');
        }

        return $nativeCoins[$prefix];
    }

    /**
     * Token carries a prefix (for example TRC20), a native coin does not have prefix.
     *
     * @param string $ticker  [ 'BTC', 'LTC', 'TRC20/USDT',... ]
     * @return boolean
     */
    public function isToken(string $ticker): bool
    {
        return str_contains($ticker, '/');
    }

    /**
     * Response with the estimated cost in the blockchain’s native cryptocurrency. [ BTC, LTC, TRX,... ]
     *
     * @param string $ticker  [ 'BTC', 'LTC', 'TRC20/USDT',... ]
     * @return float
     */
    public function estimatedBlockchainCryptoFee(string $ticker): float
    {
        $response = $this->getAdapterInstance()->getBlockchainFee($ticker);

        if (!isset($response['estimated_cost'])) {
            throw new CryptoApiProcessorException('BlockBee did not return an estimated cost for ' . $ticker . '.');
        }

        return (float) $response['estimated_cost'];
    }

    /**
     * Response with the estimated cost in various FIAT currencies [ USD, EUR, GBP, CAD,... ].
     *
     * @param string $ticker    [ 'BTC', 'LTC', 'TRC20/USDT', ... ]
     * @param string $currency  [ 'USD', 'EUR', ... ]
     * @return float
     */
    public function estimatedBlockchainFiatFee(string $ticker, string $currency = 'USD'): float
    {
        if ($currency && !in_array(strtoupper($currency), config('blockbee.supported_fiat_currencies'))) {
            throw new CryptoApiProcessorException('Invalid fiat currency.');
        }

        $currency = strtoupper($currency);
        $response = $this->getAdapterInstance()->getBlockchainFee($ticker);

        if (!isset($response['estimated_cost_currency'][$currency])) {
            throw new CryptoApiProcessorException('BlockBee did not return an estimated cost in ' . $currency . ' for ' . $ticker . '.');
        }

        return (float) $response['estimated_cost_currency'][$currency];
    }
}
