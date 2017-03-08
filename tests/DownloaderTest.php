<?php

namespace Spatie\SslCertificate\Test;

use PHPUnit_Framework_TestCase;
use Spatie\SslCertificate\Downloader;
use Spatie\SslCertificate\SslCertificate;
use Spatie\SslCertificate\Exceptions\CouldNotDownloadCertificate;

class DownloaderTest extends PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_can_download_a_certificate_from_a_host_name()
    {
        $sslCertificate = Downloader::downloadCertificateFromUrl('spatie.be');

        $this->assertInstanceOf(SslCertificate::class, $sslCertificate);
    }

    /** @test */
    public function it_throws_an_exception_for_non_existing_host()
    {
        $this->expectException(CouldNotDownloadCertificate::class);

        Downloader::downloadCertificateFromUrl('spatie-non-existing.be');
    }

    /** @test */
    public function it_can_download_a_self_signed_certificated()
    {
        $rawCertificateFields = Downloader::downloadCertificateFromUrl('self-signed.badssl.com');

        $this->assertTrue(is_array($rawCertificateFields));

        $this->assertSame('/C=US/ST=California/L=San Francisco/O=BadSSL/CN=*.badssl.com', $rawCertificateFields['name']);
    }

    /** @test */
    public function it_can_download_a_expired_certificated()
    {
        $rawCertificateFields = Downloader::downloadCertificateFromUrl('expired.badssl.com');

        $this->assertTrue(is_array($rawCertificateFields));

        $this->assertSame('/OU=Domain Control Validated/OU=PositiveSSL Wildcard/CN=*.badssl.com', $rawCertificateFields['name']);
    }
}
