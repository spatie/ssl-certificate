<?php

namespace Spatie\SslCertificate\Test;

use PHPUnit\Framework\TestCase;
use Spatie\SslCertificate\Downloader;
use Spatie\SslCertificate\SslCertificate;
use Spatie\SslCertificate\Exceptions\CouldNotDownloadCertificate;

class DownloaderTest extends TestCase
{
    /** @test */
    public function it_can_download_a_certificate_from_a_host_name()
    {
        $sslCertificate = Downloader::downloadCertificateFromUrl('spatie.be');

        $this->assertInstanceOf(SslCertificate::class, $sslCertificate);
    }

    /** @test */
    public function it_can_download_all_certificates_from_a_host_name()
    {
        $sslCertificates = (new Downloader)->getCertificates('spatie.be');

        $this->assertCount(1, $sslCertificates);
    }

    /** @test */
    public function it_throws_an_exception_for_non_existing_host()
    {
        $this->expectException(CouldNotDownloadCertificate::class);

        Downloader::downloadCertificateFromUrl('spatie-non-existing.be');
    }

    /** @test */
    public function it_throws_an_exception_when_downloading_a_certificate_from_a_host_that_contains_none()
    {
        $this->expectException(CouldNotDownloadCertificate::class);

        Downloader::downloadCertificateFromUrl('www.kutfilm.be');
    }
}
