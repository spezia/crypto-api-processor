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
