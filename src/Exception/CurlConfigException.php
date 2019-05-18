<?php


namespace functionsLibrary\Exception;

use InvalidArgumentException;

class CurlConfigException extends InvalidArgumentException implements FunctionsException
{
    public static function throwError($param)
    {
        return new self('Config: ' . strtoupper($param) . ' is not support' );
    }
}