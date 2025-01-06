<?php

/**
 * CryptoApiProcessor
 *
 * @author  Aleksandar Rancic <aleks.rancic@gmail.com>
 * @license MIT License
 * @link    https://github.com/spezia/crypto-api-processor
 */

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase;
use Spezia\CryptoApiProcessor\CryptoApiAdapter;
use Spezia\CryptoApiProcessor\Exceptions\CryptoApiProcessorException;
use Spezia\CryptoApiProcessor\Providers\CryptoApiProcessorServiceProvider;

class CryptoApiAdapterHelperTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Config::set('blockbee.api_key', 'test123');
    }

    protected function getPackageProviders($app)
    {
        return [CryptoApiProcessorServiceProvider::class];
    }

    public function test_has_exceed_balance()
    {
        $ticker = 'LTC';

        Http::fake([
            config('blockbee.base_url') . '/' . strtolower($ticker) . '/payout/balance/?' . http_build_query(['apikey' => 'test123'])
            => Http::response(['status' => 'success', 'balance' => 0.0215], 200),
        ]);
        Http::fake([
            config('blockbee.base_url') . '/' . strtolower($ticker) . '/info/?' . http_build_query(['apikey' => 'test123', 'prices' => 1])
            => Http::response(['status' => 'success', 'fee_percent' => '1.00'], 200),
        ]);
        Http::fake([
            config('blockbee.base_url') . '/' . strtolower($ticker) . '/estimate/?' . http_build_query(['apikey' => 'test123', 'addresses' => 1, 'priority' => 'default'])
            => Http::response(['status' => 'success', 'estimated_cost' => '0.00001', 'estimated_cost_currency' => ['EUR' => 0.099]], 200),
        ]);

        $response = (new CryptoApiAdapter)->hasExceedBalance(0.01, $ticker);
        $this->assertIsBool($response);
        $this->assertFalse($response);

        $response = (new CryptoApiAdapter)->hasExceedBalance(0.05, $ticker);
        $this->assertTrue($response);
    }

    public function test_estimated_blockchain_crypto_fee()
    {
        $ticker = 'LTC';

        Http::fake([
            config('blockbee.base_url') . '/' . strtolower($ticker) . '/estimate/?' . http_build_query(['apikey' => 'test123', 'addresses' => 1, 'priority' => 'default'])
            => Http::response(['status' => 'success', 'estimated_cost' => '0.00001', 'estimated_cost_currency' => ['EUR' => 0.099]], 200),
        ]);

        $response = (new CryptoApiAdapter)->estimatedBlockchainCryptoFee($ticker);

        $this->assertIsFloat($response);
        $this->assertEquals(0.00001, $response);
    }

    public function test_estimated_blockchain_fiat_fee()
    {
        $ticker = 'LTC';

        Http::fake([
            config('blockbee.base_url') . '/' . strtolower($ticker) . '/estimate/?' . http_build_query(['apikey' => 'test123', 'addresses' => 1, 'priority' => 'default'])
            => Http::response(['status' => 'success', 'estimated_cost' => '0.00001', 'estimated_cost_currency' => ['EUR' => 0.150]], 200),
        ]);

        $response = (new CryptoApiAdapter)->estimatedBlockchainFiatFee($ticker, 'EUR');

        $this->assertIsFloat($response);
        $this->assertEquals(0.150, $response);
    }

    public function test_estimated_blockchain_fiat_fee_exception()
    {
        $this->expectException(CryptoApiProcessorException::class);
        $this->expectExceptionMessage('Invalid fiat currency.');

        (new CryptoApiAdapter)->estimatedBlockchainFiatFee('LTC', 'RSD');
    }
}
