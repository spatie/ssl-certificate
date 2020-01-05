<?php

namespace Spatie\SslCertificate\Exceptions\CouldNotLoadLocalCertificate;


use Spatie\SslCertificate\Exceptions\CouldNotLoadLocalCertificate;
use Throwable;

class NoCertificateFoundAtPath extends CouldNotLoadLocalCertificate
{
    public function __construct(string $path)
    {
        parent::__construct("No certificate found at {$path}");
    }
}
