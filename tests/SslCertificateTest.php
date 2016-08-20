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

        Carbon::setTestNow(Carbon::create('2016', '06', '01', '00', '00', '00', 'utc'));

        $rawCertificateFields = json_decode(file_get_contents(__DIR__.'/stubs/spatieCertificateFields.json'), true);

        $this->certificate = new SslCertificate($rawCertificateFields);
    }

    /** @test */
    public function it_can_determine_the_issuer()
    {
        $this->assertSame("Let's Encrypt Authority X3", $this->certificate->getIssuer());
    }

    /** @test */
    public function it_can_determine_the_domain()
    {
        $this->assertSame('spatie.be', $this->certificate->getDomain());
    }

    /** @test */
    public function it_can_determine_the_alogorithm()
    {
        $this->assertSame('RSA-SHA256', $this->certificate->getSignatureAlgorithm());
    }

    /** @test */
    public function it_can_determine_the_additional_domains()
    {
        $this->assertCount(3, $this->certificate->getAdditionalDomains());

        $this->assertSame('spatie.be', $this->certificate->getAdditionalDomains()[0]);
        $this->assertSame('www.spatie.be', $this->certificate->getAdditionalDomains()[1]);
        $this->assertSame('*.otherdomain.com', $this->certificate->getAdditionalDomains()[2]);
    }

    /** @test */
    public function it_can_determine_the_valid_from_date()
    {
        $this->assertInstanceOf(Carbon::class, $this->certificate->validFromDate());

        $this->assertSame('2016-05-19 16:50:00', $this->certificate->validFromDate()->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function it_can_determine_the_expiration_date()
    {
        $this->assertInstanceOf(Carbon::class, $this->certificate->expirationDate());

        $this->assertSame('2016-08-17 16:50:00', $this->certificate->expirationDate()->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function it_can_determine_if_the_certificate_is_valid()
    {
        Carbon::setTestNow(Carbon::create('2016', '05', '19', '16', '45', '00', 'utc'));
        $this->assertFalse($this->certificate->isValid());

        Carbon::setTestNow(Carbon::create('2016', '05', '19', '16', '51', '00', 'utc'));
        $this->assertTrue($this->certificate->isValid());

        Carbon::setTestNow(Carbon::create('2016', '08', '17', '16', '49', '00', 'utc'));
        $this->assertTrue($this->certificate->isValid());

        Carbon::setTestNow(Carbon::create('2016', '08', '17', '16', '51', '00', 'utc'));
        $this->assertFalse($this->certificate->isValid());
    }

    /** @test */
    public function it_can_determine_if_the_certificate_is_expired()
    {
        Carbon::setTestNow(Carbon::create('2016', '05', '19', '16', '45', '00', 'utc'));
        $this->assertFalse($this->certificate->isExpired());

        Carbon::setTestNow(Carbon::create('2016', '05', '19', '16', '51', '00', 'utc'));
        $this->assertFalse($this->certificate->isExpired());

        Carbon::setTestNow(Carbon::create('2016', '08', '17', '16', '49', '00', 'utc'));
        $this->assertFalse($this->certificate->isExpired());

        Carbon::setTestNow(Carbon::create('2016', '08', '17', '16', '51', '00', 'utc'));
        $this->assertTrue($this->certificate->isExpired());
    }

    /** @test */
    public function it_can_determine_if_the_certificate_is_valid_until_a_date()
    {
        // Expire date of certificate is: 17/08/2016 16:50
        Carbon::setTestNow(Carbon::create('2016', '08', '10', '16', '49', '00', 'utc'));     // 10/08   16:49
        $this->assertFalse($this->certificate->isValidUntil(Carbon::now()->addDays(2)));     // 12/08

        Carbon::setTestNow(Carbon::create('2016', '08', '10', '16', '49', '00', 'utc'));     // 10/08   16:49
        $this->assertTrue($this->certificate->isValidUntil(Carbon::now()->addDays(8)));      // 18/08

        Carbon::setTestNow(Carbon::create('2016', '08', '16', '16', '49', '00', 'utc'));     // 16/08   16:49
        $this->assertFalse($this->certificate->isValidUntil(Carbon::now()->addDays(1)));     // 17/08

        Carbon::setTestNow(Carbon::create('2016', '08', '16', '16', '51', '00', 'utc'));     // 16/08   16:51
        $this->assertTrue($this->certificate->isValidUntil(Carbon::now()->addDays(1)));      // 17/08

        Carbon::setTestNow(Carbon::create('2016', '08', '17', '16', '49', '00', 'utc'));     // 17/08   16:49
        $this->assertTrue($this->certificate->isValidUntil(Carbon::now()->addDays(1)));      // 18/08

        Carbon::setTestNow(Carbon::create('2016', '08', '17', '16', '51', '00', 'utc'));     // 17/08   16:51
        $this->assertFalse($this->certificate->isValidUntil(Carbon::now()->addDays(1)));     // 17/08
    }

    /** @test */
    public function it_can_determine_if_the_certificate_is_valid_for_a_certain_domain()
    {
        $this->assertTrue($this->certificate->isValid('spatie.be'));

        $this->assertTrue($this->certificate->isValid('www.spatie.be'));

        $this->assertFalse($this->certificate->isValid('another.spatie.be'));

        $this->assertTrue($this->certificate->isValid('otherdomain.com'));

        $this->assertTrue($this->certificate->isValid('www.otherdomain.com'));

        $this->assertTrue($this->certificate->isValid('another.otherdomain.com'));

        $this->assertFalse($this->certificate->isValid('facebook.com'));
    }

    /** @test */
    public function it_can_create_an_instance_for_the_given_host()
    {
        $downloadedCertificate = SslCertificate::createForHostName('spatie.be');

        $this->assertSame('spatie.be', $downloadedCertificate->getDomain());
    }
}
