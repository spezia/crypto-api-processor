<?php

/**
 * CryptoApiProcessor
 *
 * @author  Aleksandar Rancic <aleks.rancic@gmail.com>
 * @license MIT License
 * @link    https://github.com/spezia/crypto-api-processor
 */

namespace Spezia\CryptoApiProcessor\Providers;

use Illuminate\Support\ServiceProvider;

class CryptoApiProcessorServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/blockbee.php' => config_path('blockbee.php'),
        ], 'blockbee-config');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/blockbee.php',
            'blockbee' // Alias for the configuration
        );
    }
}
