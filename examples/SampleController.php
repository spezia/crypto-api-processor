<?php

namespace App\Http\Controllers;

use Spezia\CryptoApiProcessor\CryptoApiAdapter;
use Spezia\CryptoApiProcessor\Exceptions\CryptoApiProcessorException;
use Spezia\CryptoApiProcessor\Helpers\CryptoApiAdapterHelper;

/**
 * A couple of examples of how to use the CryptoApiAdapter
 * Copy this controller to your laravel app to test the CryptoApiAdapter
 */
class SampleController extends Controller
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
        $response = $this->estimatedBlockchainFiatFee($ticker, 'USD');

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
