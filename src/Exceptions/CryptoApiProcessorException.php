<?php

/**
 * CryptoApiProcessor
 *
 * @author  Aleksandar Rancic <aleks.rancic@gmail.com>
 * @license MIT License
 * @link    https://github.com/spezia/crypto-api-processor
 */

namespace Spezia\CryptoApiProcessor\Exceptions;

use Exception;
use Throwable;

class CryptoApiProcessorException extends Exception
{
    public function __construct($message = "", $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
