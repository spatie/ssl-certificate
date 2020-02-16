<?php

namespace Spatie\SslCertificate\Test;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;
use Spatie\SslCertificate\SslCertificate;

class SslCertificateFromStringTest extends TestCase
{
    use MatchesSnapshots;

    /** @var SslCertificate */
    protected $certificate;

    public function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create('2020', '01', '13', '03', '18', '13', 'utc'));

        $certificate = file_get_contents(__DIR__.'/stubs/spatieCertificate.pem');

        $this->certificate = SslCertificate::createFromString($certificate);
    }

    /** @test */
    public function it_can_determine_the_issuer()
    {
        $this->assertSame("Let's Encrypt Authority X3", $this->certificate->getIssuer());
    }

    /** @test */
    public function it_can_determine_the_domain()
    {
        $this->assertSame('analytics.spatie.be', $this->certificate->getDomain());
    }

    /** @test */
    public function it_can_determine_the_signature_algorithm()
    {
        $this->assertSame('RSA-SHA256', $this->certificate->getSignatureAlgorithm());
    }

    /** @test */
    public function it_can_determine_the_additional_domains()
    {
        $this->assertCount(1, $this->certificate->getAdditionalDomains());

        $this->assertSame('analytics.spatie.be', $this->certificate->getAdditionalDomains()[0]);
    }

    /** @test */
    public function it_can_determine_the_valid_from_date()
    {
        $this->assertInstanceOf(Carbon::class, $this->certificate->validFromDate());

        $this->assertSame('2020-01-13 03:18:13', $this->certificate->validFromDate()->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function it_can_determine_the_expiration_date()
    {
        $this->assertInstanceOf(Carbon::class, $this->certificate->expirationDate());

        $this->assertSame('2020-04-12 03:18:13', $this->certificate->expirationDate()->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function it_can_determine_if_the_certificate_is_valid()
    {
        Carbon::setTestNow(Carbon::create('2016', '05', '19', '16', '45', '00', 'utc'));
        $this->assertFalse($this->certificate->isValid());

        Carbon::setTestNow(Carbon::create('2020', '02', '13', '16', '51', '00', 'utc'));
        $this->assertTrue($this->certificate->isValid());

        Carbon::setTestNow(Carbon::create('2020', '03', '17', '16', '49', '00', 'utc'));
        $this->assertTrue($this->certificate->isValid());

        Carbon::setTestNow(Carbon::create('2016', '08', '17', '16', '51', '00', 'utc'));
        $this->assertFalse($this->certificate->isValid());
    }

    /** @test */
    public function it_can_determine_if_the_certificate_is_expired()
    {
        Carbon::setTestNow(Carbon::create('2020', '02', '13', '16', '45', '00', 'utc'));
        $this->assertFalse($this->certificate->isExpired());

        Carbon::setTestNow(Carbon::create('2020', '02', '13', '16', '51', '00', 'utc'));
        $this->assertFalse($this->certificate->isExpired());

        Carbon::setTestNow(Carbon::create('2020', '02', '17', '16', '49', '00', 'utc'));
        $this->assertFalse($this->certificate->isExpired());

        Carbon::setTestNow(Carbon::create('2020', '08', '17', '16', '51', '00', 'utc'));
        $this->assertTrue($this->certificate->isExpired());
    }

    /** @test */
    public function it_provides_a_fluent_interface_to_set_all_options()
    {
        $downloadedCertificate = SslCertificate::download()
            ->usingPort(443)
            ->setTimeout(30)
            ->forHost('spatie.be');

        $this->assertSame('spatie.be', $downloadedCertificate->getDomain());
    }

    /** @test */
    public function it_can_convert_the_certificate_to_json()
    {
        $this->assertMatchesJsonSnapshot($this->certificate->getRawCertificateFieldsJson());
    }

    /** @test */
    public function it_can_convert_the_certificate_to_a_string()
    {
        $this->assertEquals(
            $this->certificate->getRawCertificateFieldsJson(),
            (string) $this->certificate
        );
    }

    /** @test */
    public function it_can_get_the_hash_of_a_certificate()
    {
        $this->assertEquals('0547c1a78dcdbe96f907aaaf42db5b8f', $this->certificate->getHash());
    }

    /** @test */
    public function it_can_get_all_domains()
    {
        $this->assertEquals([
            0 => 'analytics.spatie.be',
        ], $this->certificate->getDomains());
    }

    /** @test */
    public function it_can_get_the_days_until_the_expiration_date()
    {
        $this->assertEquals(90, $this->certificate->daysUntilExpirationDate());
    }

    /** @test */
    public function it_can_determine_if_it_is_self_signed()
    {
        $this->assertFalse($this->certificate->isSelfSigned());
    }

    /** @test */
    public function it_can_determine_if_it_uses_sha1_hasing()
    {
        $this->assertFalse($this->certificate->usesSha1Hash());
    }

    /** @test */
    public function it_can_determine_if_the_certificate_has_a_certain_domain()
    {
        $this->assertTrue($this->certificate->containsDomain('analytics.spatie.be'));

        $this->assertFalse($this->certificate->containsDomain('www.example.com'));
        $this->assertFalse($this->certificate->containsDomain('notreallyspatie.be'));
        $this->assertFalse($this->certificate->containsDomain('spatie.be.example.com'));
    }

    /** @test */
    public function does_not_notify_on_wrong_domains()
    {
        $rawCertificateFields = json_decode(
            file_get_contents(__DIR__.'/stubs/certificateWithRandomWildcardDomains.json'),
            true
        );

        $this->certificate = new SslCertificate($rawCertificateFields);

        $this->assertFalse($this->certificate->appliesToUrl('https://coinmarketcap.com'));
    }

    /** @test */
    public function it_correctly_compares_uppercase_domain_names()
    {
        $rawCertificateFields = json_decode(
            file_get_contents(__DIR__.'/stubs/certificateWithUppercaseDomains.json'),
            true
        );

        $this->certificate = new SslCertificate($rawCertificateFields);

        $this->assertTrue($this->certificate->appliesToUrl('spatie.be'));
        $this->assertTrue($this->certificate->appliesToUrl('www.spatie.be'));
    }

    /** @test */
    public function it_correctly_identifies_pre_certificates()
    {
        $rawCertificateFieldsNormalCertificate = json_decode(
            file_get_contents(__DIR__.'/stubs/spatieCertificateFields.json'),
            true
        );

        $rawCertificateFieldsPreCertificate = json_decode(
            file_get_contents(__DIR__.'/stubs/preCertificate.json'),
            true
        );

        $certificateNormal = new SslCertificate($rawCertificateFieldsNormalCertificate);
        $certificatePreCertificate = new SslCertificate($rawCertificateFieldsPreCertificate);

        $this->assertFalse($certificateNormal->isPreCertificate());
        $this->assertTrue($certificatePreCertificate->isPreCertificate());
    }
}
