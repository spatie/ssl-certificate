<?php

namespace Spatie\SslCertificate\Exceptions\CouldNotLoadLocalCertificate;

use Spatie\SslCertificate\Exceptions\CouldNotLoadLocalCertificate;

class CertificateFileReadFailed extends CouldNotLoadLocalCertificate
{
    public function __construct(string $path)
    {
        parent::__construct("Failed to open the certificate file at {$path}");
    }
}
