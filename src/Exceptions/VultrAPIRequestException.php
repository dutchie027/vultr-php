<?php

namespace dutchie027\Vultr\Exceptions;

class VultrAPIRequestException extends VultrAPIException
{
    public function __construct($message = '', $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
