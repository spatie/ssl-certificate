<?php

namespace Spatie\SslCertificate\Test;

use PHPUnit\Framework\TestCase;
use Spatie\SslCertificate\Exceptions\CouldNotLoadLocalCertificate\CertificateStringEmpty;
use Spatie\SslCertificate\Local;
use Spatie\SslCertificate\SslCertificate;

class LocalTest extends TestCase
{
    private function getCertificate($path):string
    {
        return file_get_contents(__DIR__ . '/stubs/' . $path);
    }

    /** @test */
    public function it_can_parse_a_valid_certificate()
    {
        $certificateString = $this->getCertificate('validCertificate.cert');
        $sslCertificate = Local::certificateAsString($certificateString);
        $this->assertInstanceOf(SslCertificate::class, $sslCertificate);
    }

    /** @test */
    public function it_cannot_parse_a_empty_certificate()
    {
        $this->expectException(CertificateStringEmpty::class);
        Local::certificateAsString('');
    }

    public function it_can_find_a_certificate_from_valid_path()
    {}

    public function it_cannot_find_a_certificate_from_non_existing_path()
    {}

    public function it_can_read_from_a_certificate_file_with_permission()
    {}

    public function it_cannot_read_from_a_certificate_file_without_permission()
    {}

    public function it_cannot_parse_a_invalid_certificate()
    {}
}