<?php

namespace Spatie\SslCertificate\Test;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;
use Spatie\SslCertificate\SslCertificate;

class SslCertificateFromFileTest extends TestCase
{
    use MatchesSnapshots;

    /** @var Spatie\SslCertificate\SslCertificate */
    protected $certificate;


    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create('2020', '01', '13', '03', '18', '13', 'utc'));


    }

    /** @test */
    public function it_can_load_pem_certificate()
    {
        $this->certificate = SslCertificate::createFromFile(__DIR__.'/stubs/spatieCertificate.pem');
        $this->assertSame("Let's Encrypt", $this->certificate->getOrganization());
        $this->assertSame("analytics.spatie.be", $this->certificate->getDomain());
    }

    /** @test */
    public function it_can_load_der_certificate()
    {
        $this->certificate = SslCertificate::createFromFile(__DIR__.'/stubs/derCertificate.der');
        $this->assertSame("Let's Encrypt", $this->certificate->getOrganization());
        $this->assertSame("analytics.spatie.be", $this->certificate->getDomain());
    }

}
