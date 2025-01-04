<?php

namespace Spezia\CryptoApiProcessor\Tests;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase;
use Spezia\CryptoApiProcessor\CryptoApiAdapter;
use Spezia\CryptoApiProcessor\Exceptions\CryptoApiProcessorException;
use Spezia\CryptoApiProcessor\Providers\CryptoApiProcessorServiceProvider;

class CryptoApiAdapterTest extends TestCase
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

    public function test_get_base_url()
    {
        $response = (new CryptoApiAdapter)->getBaseUrl();
        $this->assertIsString($response);
    }

    public function test_get_info()
    {
        Http::fake([
            '*' => Http::response(['cryptocurrency' => ["ticker" => "btc", "minimum_transaction" => 8000]], 200),
        ]);

        $response = (new CryptoApiAdapter)->getInfo();

        $this->assertIsArray($response);
        $this->assertArrayHasKey('cryptocurrency', $response);
        $this->assertEquals('btc', $response['cryptocurrency']['ticker']);
    }

    public function test_get()
    {
        $response = [
            'cryptocurrency' => ["ticker" => "btc", "minimum_transaction" => 8000]
        ];
        Http::fake([
            '*' => Http::response($response, 200),
        ]);

        $response = (new CryptoApiAdapter)->get(config('blockbee.base_url'));

        $this->assertIsArray($response);
        $this->assertArrayHasKey('cryptocurrency', $response);
        $this->assertEquals('btc', $response['cryptocurrency']['ticker']);
    }

    public function test_get_exception()
    {
        $this->expectException(CryptoApiProcessorException::class);
        $this->expectExceptionMessage('Error fetching info.');

        Http::fake([
            '*' => Http::response([], 500),
        ]);

        (new CryptoApiAdapter)->get(config('blockbee.base_url'));
    }

    public function test_get_info_exception()
    {
        $this->expectException(CryptoApiProcessorException::class);
        $this->expectExceptionMessage('Prices must be either 0 or 1.');

        $hasPrices = 2;
        (new CryptoApiAdapter)->getInfo($hasPrices);
    }

    public function test_post_exception()
    {
        $this->expectException(CryptoApiProcessorException::class);
        $this->expectExceptionMessage('Your request could not be processed, please try again later');

        Http::fake([
            '*' => Http::response([], 500),
        ]);

        (new CryptoApiAdapter)->post(config('blockbee.base_url'), []);
    }

    public function test_post_request()
    {
        Http::fake([
            '*' => Http::response(['data' => 'test'], 200),
        ]);

        $response = (new CryptoApiAdapter)->post(config('blockbee.base_url'), []);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_get_info_by_ticker_exception()
    {
        $this->expectException(CryptoApiProcessorException::class);
        $this->expectExceptionMessage('Prices must be either 0 or 1.');

        $hasPrices = 2;
        (new CryptoApiAdapter)->getInfoByTicker('btc', $hasPrices);
    }

    public function test_get_info_by_ticker()
    {
        Http::fake([
            '*' => Http::response(['coin' => 'Bitcoin'], 200),
        ]);

        $response = (new CryptoApiAdapter)->getInfoByTicker('btc');

        $this->assertIsArray($response);
        $this->assertArrayHasKey('coin', $response);
    }

    public function test_get_qrcode()
    {
        Http::fake([
            '*' => Http::response(['status' => 'success', 'qr_code' => '...', 'payment_uri' => '...'], 200),
        ]);

        $response = (new CryptoApiAdapter)->getQRCode('btc', 'x123', 100);

        $this->assertIsArray($response);
        $this->assertCount(3, $response);
        $this->assertArrayHasKey('status', $response);
    }

    public function test_get_new_address()
    {
        Http::fake([
            '*' => Http::response(['status' => 'success', 'address_in' => 'x123'], 200),
        ]);

        $response = (new CryptoApiAdapter)->getNewAddress('btc', 'https://example.com/callback/product/123');

        $this->assertIsArray($response);
        $this->assertArrayHasKey('address_in', $response);
    }

    public function test_status_payout()
    {
        Http::fake([
            '*' => Http::response(['status' => 'success', 'payout_info' => ['status' => 'done']], 200),
        ]);

        $response = (new CryptoApiAdapter)->statusPayout('x07123456789');

        $this->assertIsArray($response);
        $this->assertArrayHasKey('payout_info', $response);
    }

    public function test_process_payout()
    {
        Http::fake([
            '*' => Http::response(['status' => 'success', 'request_ids' => ['e7408219-b37c-4e24-8a71-78f0755468d8']], 200),
        ]);

        $response = (new CryptoApiAdapter)->processPayout('btc', 'x123', 100.00);

        $this->assertIsArray($response);
        $this->assertCount(2, $response);
        $this->assertArrayHasKey('request_ids', $response);
    }

    public function test_fetch_total_balance()
    {
        Http::fake([
            '*' => Http::response(['status' => 'success', 'balance' => 0.02], 200),
        ]);

        $response = (new CryptoApiAdapter)->fetchTotalBalance('btc');

        $this->assertIsFloat($response);
        $this->assertEquals(0.02, $response);
    }

    public function test_get_blockchain_fee()
    {
        Http::fake([
            '*' => Http::response(['status' => 'success', 'estimated_cost' => 0.0001], 200),
        ]);

        $response = (new CryptoApiAdapter)->getBlockchainFee('btc');

        $this->assertIsArray($response);
        $this->assertArrayHasKey('estimated_cost', $response);
        $this->assertEquals(0.0001, $response['estimated_cost']);
    }
}
