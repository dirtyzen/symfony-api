<?php


namespace App\Exception;


use Throwable;

class InvalidConfirmationTokenException extends \Exception
{

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct('Invalid confirmation token.', $code, $previous);
    }

}