<?php

namespace Spatie\SslCertificate\Test;

use Carbon\Carbon;
use PHPUnit_Framework_TestCase;
use Spatie\SslCertificate\SslCertificate;

class SslCertificateTest extends PHPUnit_Framework_TestCase
{
    /** @var SslCertificate */
    protected $certificate;

    public function setUp()
    {
        parent::setUp();

        $rawCertificateFields = json_decode(file_get_contents(__DIR__.'/stubs/spatieCertificateFields.json'), true);

        $this->certificate = new SslCertificate($rawCertificateFields);
    }

    /** @test */
    public function it_can_determine_the_domain()
    {
        $this->assertSame('spatie.be', $this->certificate->getDomain());
    }

    /** @test */
    public function it_can_determine_the_additional_domains()
    {
        $this->assertCount(2, $this->certificate->getAdditionalDomains());

        $this->assertSame('spatie.be', $this->certificate->getAdditionalDomains()[0]);
        $this->assertSame('www.spatie.be', $this->certificate->getAdditionalDomains()[1]);
    }

    /** @test */
    public function it_can_determine_the_expiration_date()
    {
        $this->assertInstanceOf(Carbon::class, $this->certificate->getExpirationDate());

        $this->assertSame('2016-08-17 16:50:00', $this->certificate->getExpirationDate()->format('Y-m-d H:i:s'));
    }
}
