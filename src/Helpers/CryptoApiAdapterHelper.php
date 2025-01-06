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
        $fees = $adapter->getBlockchainFee($ticker);
        $blockchainFee = (float) $fees['estimated_cost'];

        return $amount + $blockchainFee + ($amount / 100 * (float) $info['fee_percent']) > $balance;
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

        return (float) $response['estimated_cost_currency'][$currency];
    }
}
