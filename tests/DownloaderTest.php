<?php

namespace Spatie\SslCertificate\Test;

use PHPUnit_Framework_TestCase;
use Spatie\SslCertificate\Downloader;

class DownloaderTest extends PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_can_download_a_certificate_from_a_host_name()
    {
        $rawCertificateFields = Downloader::downloadCertificateFromUrl('spatie.be');

        $this->assertTrue(is_array($rawCertificateFields));

        $this->assertSame('/CN=spatie.be', $rawCertificateFields['name']);
    }
}
