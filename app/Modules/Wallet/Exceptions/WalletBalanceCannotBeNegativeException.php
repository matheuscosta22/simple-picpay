<?php

namespace App\Modules\Wallet\Exceptions;

use Exception;

class WalletBalanceCannotBeNegativeException extends Exception
{
    public function __construct(string $message = "Wallet cannot be negative", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
