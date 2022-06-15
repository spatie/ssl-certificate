<?php

namespace Spatie\SslCertificate\Test;

use PHPUnit\Framework\TestCase;
use Spatie\SslCertificate\Downloader;
use Spatie\SslCertificate\Exceptions\CouldNotDownloadCertificate\HostDoesNotExist;
use Spatie\SslCertificate\Exceptions\CouldNotDownloadCertificate\UnknownError;
use Spatie\SslCertificate\SslCertificate;

class DownloaderTest extends TestCase
{
    protected $domainWithDifferentPort;
    protected $ipDomainWithDifferentPort;
    protected $differentPort;

    protected function setUp(): void
    {
        parent::setUp();

        $this->domainWithDifferentPort = 'ben.hotweb.de';
        $this->ipDomainWithDifferentPort = '178.254.8.25';
        $this->differentPort = 8443;
    }

    /** @test */
    public function it_can_download_a_certificate_from_a_host_name()
    {
        $sslCertificate = Downloader::downloadCertificateFromUrl('spatie.be');

        $this->assertInstanceOf(SslCertificate::class, $sslCertificate);
    }

    /** @test */
    public function it_can_download_a_certificate_from_a_host_name_with_hostport()
    {
        $sslCertificate = Downloader::downloadCertificateFromUrl($this->domainWithDifferentPort . ':' . $this->differentPort);

        $this->assertInstanceOf(SslCertificate::class, $sslCertificate);
    }

    /** @test */
    public function it_can_download_a_certificate_from_a_host_name_with_strange_characters()
    {
        $sslCertificate = Downloader::downloadCertificateFromUrl('https://www.hüpfburg.de');

        $this->assertInstanceOf(SslCertificate::class, $sslCertificate);
    }

    /** @test */
    public function it_can_download_a_certificate_from_a_host_name_with_strange_characters_with_hostport()
    {
        $sslCertificate = Downloader::downloadCertificateFromUrl('https://' . $this->domainWithDifferentPort . ':' . $this->differentPort);

        $this->assertInstanceOf(SslCertificate::class, $sslCertificate);
    }

    /** @test */
    public function it_can_download_a_certificate_for_a_host_name_from_an_ip_address()
    {
        $sslCertificate = SslCertificate::download()
            ->fromIpAddress('164.92.244.169')
            ->forHost('spatie.be');

        $this->assertInstanceOf(SslCertificate::class, $sslCertificate);
    }

    /** @test */
    public function it_can_download_a_certificate_for_a_host_name_from_an_ip_address_with_hostport()
    {
        $sslCertificate = SslCertificate::download()
            ->fromIpAddress($this->ipDomainWithDifferentPort)
            ->forHost($this->domainWithDifferentPort . ':' . $this->differentPort);

        $this->assertInstanceOf(SslCertificate::class, $sslCertificate);
    }

    /** @test */
    public function it_can_download_a_certificate_for_a_host_name_from_an_ipv6_address()
    {
        $sslCertificate = SslCertificate::download()
            ->fromIpAddress('2a00:1450:4001:80e::200e')
            ->forHost('google.com');

        $this->assertInstanceOf(SslCertificate::class, $sslCertificate);
    }

    /** @test */
    public function it_sets_a_fingerprint_on_the_downloaded_certificate()
    {
        $sslCertificate = Downloader::downloadCertificateFromUrl('spatie.be');

        $this->assertNotEmpty($sslCertificate->getFingerprint());
    }

    /** @test */
    public function it_sets_a_fingerprint_on_the_downloaded_certificate_with_hostport()
    {
        $sslCertificate = Downloader::downloadCertificateFromUrl($this->domainWithDifferentPort . ':' . $this->differentPort);

        $this->assertNotEmpty($sslCertificate->getFingerprint());
    }

    /** @test */
    public function it_can_download_all_certificates_from_a_host_name()
    {
        $sslCertificates = (new Downloader())->getCertificates('spatie.be');

        $this->assertCount(1, $sslCertificates);
    }

    /** @test */
    public function it_can_download_all_certificates_from_a_host_name_with_socket_context_options()
    {
        $sslCertificates = (new Downloader())
            ->withSocketContextOptions([
                'bindto' => '0:0',
            ])
            ->getCertificates('spatie.be');

        $this->assertCount(1, $sslCertificates);
    }

    /** @test */
    public function it_throws_an_exception_for_non_existing_host()
    {
        $this->expectException(HostDoesNotExist::class);

        Downloader::downloadCertificateFromUrl('spatie-non-existing.be');
    }

    /** @test */
    public function it_throws_an_exception_when_downloading_a_certificate_from_a_host_that_contains_none()
    {
        $this->expectException(UnknownError::class);

        Downloader::downloadCertificateFromUrl('3564020356.org');
    }

    /** @test */
    public function it_throws_an_exception_when_downloading_a_certificate_for_a_missing_host_name_from_an_ip_address()
    {
        $this->expectException(UnknownError::class);

        $sslCertificate = SslCertificate::download()
            ->fromIpAddress('138.197.187.74')
            ->forHost('fake.subdomain.spatie.be');
    }

    /** @test */
    public function it_can_retrieve_the_ip_address_of_the_server_that_served_the_certificates()
    {
        $sslCertificate = Downloader::downloadCertificateFromUrl('spatie.be');

        $this->assertEquals('164.92.244.169:443', $sslCertificate->getRemoteAddress());
    }

    /** @test */
    public function it_can_retrieve_the_ip_address_of_the_server_that_served_the_certificates_with_hostport()
    {
        $sslCertificate = Downloader::downloadCertificateFromUrl($this->domainWithDifferentPort . ':' . $this->differentPort);

        $this->assertEquals($this->ipDomainWithDifferentPort . ':' . $this->differentPort, $sslCertificate->getRemoteAddress());
    }
}
