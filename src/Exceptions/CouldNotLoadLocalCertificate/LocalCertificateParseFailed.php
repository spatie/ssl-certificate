<?php

namespace Spatie\SslCertificate\Exceptions\CouldNotLoadLocalCertificate;

use Spatie\SslCertificate\Exceptions\CouldNotLoadLocalCertificate;

class LocalCertificateParseFailed extends CouldNotLoadLocalCertificate
{
    public function __construct()
    {
        parent::__construct('Failed to parse the local certificate file');
    }
}
