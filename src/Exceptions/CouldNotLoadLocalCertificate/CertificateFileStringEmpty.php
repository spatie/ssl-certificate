<?php

namespace Spatie\SslCertificate\Exceptions\CouldNotLoadLocalCertificate;

use Spatie\SslCertificate\Exceptions\CouldNotLoadLocalCertificate;

class CertificateFileStringEmpty extends CouldNotLoadLocalCertificate
{
    public function __construct()
    {
        parent::__construct("Input certificate string given is empty");
    }
}