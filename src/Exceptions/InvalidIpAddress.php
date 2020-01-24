<?php

namespace Spatie\SslCertificate\Exceptions;

use Exception;

class InvalidIpAddress extends Exception
{
    public static function couldNotValidate(string $ipAddress): self
    {
        return new static("String `{$ipAddress}` is not a valid IP address.");
    }
}
