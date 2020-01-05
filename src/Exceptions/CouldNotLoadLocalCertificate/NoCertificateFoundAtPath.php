<?php

namespace Spatie\SslCertificate\Exceptions\CouldNotLoadLocalCertificate;

use Spatie\SslCertificate\Exceptions\CouldNotLoadLocalCertificate;

class NoCertificateFoundAtPath extends CouldNotLoadLocalCertificate
{
    public function __construct(string $path)
    {
        parent::__construct("No certificate found at {$path}");
    }
}
