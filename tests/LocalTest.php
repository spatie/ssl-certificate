<?php

namespace Spatie\SslCertificate\Test;

use PHPUnit\Framework\TestCase;
use Spatie\SslCertificate\Local;
use Spatie\SslCertificate\SslCertificate;
use Spatie\SslCertificate\Exceptions\CouldNotLoadLocalCertificate\NoCertificateFoundAtPath;
use Spatie\SslCertificate\Exceptions\CouldNotLoadLocalCertificate\CertificateFileStringEmpty;
use Spatie\SslCertificate\Exceptions\CouldNotLoadLocalCertificate\LocalCertificateParseFailed;
use Spatie\SslCertificate\Exceptions\CouldNotLoadLocalCertificate\CertificateFilePermissionInvalid;

class LocalTest extends TestCase
{
    private function getCertificate($path): string
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
        $this->expectException(CertificateFileStringEmpty::class);
        Local::certificateAsString('');
    }

    /** @test */
    public function it_cannot_parse_a_certificate_with_space_and_empty()
    {
        $this->expectException(CertificateFileStringEmpty::class);
        Local::certificateAsString('    ');
    }

    /** @test */
    public function it_can_find_a_certificate_from_valid_path()
    {
        $sslCertificate = Local::certificateFromLocalPath(__DIR__ . '/stubs/validCertificate.cert');
        $this->assertInstanceOf(SslCertificate::class, $sslCertificate);
    }

    /** @test */
    public function it_cannot_find_a_certificate_from_non_existing_path()
    {
        $this->expectException(NoCertificateFoundAtPath::class);
        Local::certificateFromLocalPath(__DIR__ . '/stubs/validCertificate1.cert');
    }

    /** @test */
    public function it_cannot_parse_a_invalid_certificate()
    {
        $this->expectException(LocalCertificateParseFailed::class);
        Local::certificateFromLocalPath(__DIR__ . '/stubs/invalidCertificate.cert');
    }

    /** @test */
    public function it_can_read_from_a_certificate_file_with_permission()
    {
        $sslCertificate = Local::certificateFromLocalPath(__DIR__ . '/stubs/validCertificate.cert');
        $this->assertInstanceOf(SslCertificate::class, $sslCertificate);
    }

    /** @test */
    public function it_cannot_read_from_a_certificate_file_without_permission()
    {
        $this->expectException(CertificateFilePermissionInvalid::class);
        $file = __DIR__ . '/stubs/invalidPermissionCertificate.cert';
        chmod($file, 0000);
        Local::certificateFromLocalPath($file);
    }
}