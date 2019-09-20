<?php

namespace App\Exception;

use Throwable;

class EmptyBodyException extends \Exception
{

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct('Body is empty! Please check for it. (Json verisi yok)', $code, $previous);
    }

}