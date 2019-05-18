<?php


namespace functionsLibrary\Exception;

use InvalidArgumentException;

class CurlParamsException extends InvalidArgumentException implements FunctionsException
{
    public static function throwError($param)
    {
        return new self('Parameters: ' . strtoupper($param) . ' is not support.(POST,PUT,DELETE,PATCH)' );
    }
}