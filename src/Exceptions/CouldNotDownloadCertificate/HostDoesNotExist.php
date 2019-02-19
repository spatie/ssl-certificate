<?php

namespace Spatie\SslCertificate\Exceptions\CouldNotDownloadCertificate;

use Throwable;

class HostDoesNotExist extends CouldNotDownloadCertificate
{
    public function __construct(string $hostName)
    {
        parent::__construct("The host named `{$hostName}` does not exist.");
    }
}