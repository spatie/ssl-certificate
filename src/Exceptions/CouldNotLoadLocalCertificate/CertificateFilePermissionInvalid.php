<?php

namespace Spatie\SslCertificate\Exceptions\CouldNotLoadLocalCertificate;

use Spatie\SslCertificate\Exceptions\CouldNotLoadLocalCertificate;

class CertificateFilePermissionInvalid extends CouldNotLoadLocalCertificate
{
    public function __construct(string $path)
    {
        parent::__construct("Invalid file permission for {$path}");
    }
}
