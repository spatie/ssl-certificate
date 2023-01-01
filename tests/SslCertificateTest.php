<?php

namespace Spatie\SslCertificate\Test;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;
use Spatie\SslCertificate\Downloader;
use Spatie\SslCertificate\SslCertificate;

beforeEach(function () {
    Carbon::setTestNow(Carbon::create('2016', '06', '01', '00', '00', '00', 'utc'));

    $rawCertificateFields = json_decode(file_get_contents(__DIR__ . '/stubs/spatieCertificateFields.json'), true);

    $this->certificate = new SslCertificate($rawCertificateFields);

    $this->domainWithDifferentPort = 'psd2.b2b.belfius.be';
    $this->differentPort = 8443;
});

it('can get the raw certificate fields', function () {
    $rawCertificateFields = $this->certificate->getRawCertificateFields();

    $expectedFields = json_decode(file_get_contents(__DIR__ . '/stubs/spatieCertificateFields.json'));

    expect($rawCertificateFields)->toEqual($expectedFields);
});

it('can determine the issuer')
    ->expect(fn () => $this->certificate->getIssuer())
    ->toEqual("Let's Encrypt Authority X3");

it('can determine the serialnumber')
    ->expect(fn () => $this->certificate->getSerialNumber())
    ->toEqual('267977138471675133728493439824231787816484');

it('can determine the domain')
    ->expect(fn () => $this->certificate->getDomain())
    ->toEqual('spatie.be');

it('can determine the signature algorithm')
    ->expect(fn () => $this->certificate->getSignatureAlgorithm())
    ->toEqual('RSA-SHA256');

    it('can determine the additional domains', function () {
       $this->assertCount(3, $this->certificate->getAdditionalDomains());

        $this->assertSame('spatie.be', $this->certificate->getAdditionalDomains()[0]);
        $this->assertSame('www.spatie.be', $this->certificate->getAdditionalDomains()[1]);
        $this->assertSame('*.otherdomain.com', $this->certificate->getAdditionalDomains()[2]);
    });

class SslCertificateTest extends TestCase
{
    /** @test */
    public function ()
    {
        
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
    public function it_can_determine_the_lifespan_in_days()
    {
        $this->assertEquals(90, $this->certificate->lifespanInDays());
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
        $this->assertTrue($this->certificate->isValidUntil(Carbon::now()->addDays(2)));     // 12/08

        Carbon::setTestNow(Carbon::create('2016', '08', '10', '16', '49', '00', 'utc'));     // 10/08   16:49
        $this->assertFalse($this->certificate->isValidUntil(Carbon::now()->addDays(8)));      // 18/08

        Carbon::setTestNow(Carbon::create('2016', '08', '16', '16', '49', '00', 'utc'));     // 16/08   16:49
        $this->assertTrue($this->certificate->isValidUntil(Carbon::now()->addDays(1)));     // 17/08

        Carbon::setTestNow(Carbon::create('2016', '08', '16', '16', '51', '00', 'utc'));     // 16/08   16:51
        $this->assertFalse($this->certificate->isValidUntil(Carbon::now()->addDays(1)));      // 17/08

        Carbon::setTestNow(Carbon::create('2016', '08', '17', '16', '49', '00', 'utc'));     // 17/08   16:49
        $this->assertFalse($this->certificate->isValidUntil(Carbon::now()->addDays(1)));      // 18/08

        Carbon::setTestNow(Carbon::create('2016', '08', '17', '16', '51', '00', 'utc'));     // 17/08   16:51
        $this->assertFalse($this->certificate->isValidUntil(Carbon::now()->addDays(1)));     // 17/08
    }

    /** @test */
    public function it_can_determine_if_the_certificate_is_valid_for_a_certain_domain()
    {
        $this->assertTrue($this->certificate->isValid('spatie.be'));
        $this->assertTrue($this->certificate->isValid('www.spatie.be'));
        $this->assertFalse($this->certificate->isValid('another.spatie.be'));
        $this->assertFalse($this->certificate->isValid('www.another.spatie.be'));
        $this->assertFalse($this->certificate->isValid('another.www.another.spatie.be'));
        $this->assertTrue($this->certificate->isValid('otherdomain.com'));
        $this->assertTrue($this->certificate->isValid('www.otherdomain.com'));
        $this->assertTrue($this->certificate->isValid('another.otherdomain.com'));
        $this->assertFalse($this->certificate->isValid('www.another.otherdomain.com'));
        $this->assertFalse($this->certificate->isValid('another.www.another.otherdomain.com'));
        $this->assertFalse($this->certificate->isValid('facebook.com'));
        $this->assertFalse($this->certificate->isValid('spatie.be.facebook.com'));
        $this->assertFalse($this->certificate->isValid('www.spatie.be.facebook.com'));
    }

    /** @test */
    public function it_can_create_an_instance_for_the_given_host()
    {
        $downloadedCertificate = SslCertificate::createForHostName('spatie.be');

        $this->assertSame('spatie.be', $downloadedCertificate->getDomain());
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
    public function it_provides_a_fluent_interface_to_set_all_options_with_hostport()
    {
        $downloadedCertificate = SslCertificate::download()
            ->setTimeout(30)
            ->forHost($this->domainWithDifferentPort . ':' . $this->differentPort);

        $this->assertSame($this->domainWithDifferentPort, $downloadedCertificate->getDomain());
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
        $this->assertEquals('7469a491af5f1a5cc5dc5775608ec0ab', $this->certificate->getHash());
    }

    /** @test */
    public function it_can_get_all_domains()
    {
        $this->assertEquals([
            0 => 'spatie.be',
            1 => 'www.spatie.be',
            2 => '*.otherdomain.com',
        ], $this->certificate->getDomains());
    }

    /** @test */
    public function it_can_get_the_days_until_the_expiration_date()
    {
        $this->assertEquals(77, $this->certificate->daysUntilExpirationDate());
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
        $this->assertTrue($this->certificate->containsDomain('spatie.be'));
        $this->assertTrue($this->certificate->containsDomain('www.spatie.be'));

        $this->assertFalse($this->certificate->containsDomain('www.example.com'));
        $this->assertFalse($this->certificate->containsDomain('notreallyspatie.be'));
        $this->assertFalse($this->certificate->containsDomain('spatie.be.example.com'));
    }

    /** @test */
    public function it_can_be_encoded_as_json()
    {
        $sslCertificate = Downloader::downloadCertificateFromUrl('spatie.be');

        $serializable = serialize($sslCertificate);

        $this->assertGreaterThan(1000, strlen($serializable));

        $sslCertificate = Downloader::downloadCertificateFromUrl('www.facebook.com');

        $serializable = serialize($sslCertificate);

        $this->assertGreaterThan(1000, strlen($serializable));

        $sslCertificate = Downloader::downloadCertificateFromUrl($this->domainWithDifferentPort . ':' . $this->differentPort);

        $serializable = serialize($sslCertificate);

        $this->assertGreaterThan(1000, strlen($serializable));
    }

    /** @test */
    public function does_not_notify_on_wrong_domains()
    {
        $rawCertificateFields = json_decode(
            file_get_contents(__DIR__ . '/stubs/certificateWithRandomWildcardDomains.json'),
            true
        );

        $this->certificate = new SslCertificate($rawCertificateFields);

        $this->assertFalse($this->certificate->appliesToUrl('https://coinmarketcap.com'));
    }

    /** @test */
    public function it_correctly_compares_uppercase_domain_names()
    {
        $rawCertificateFields = json_decode(
            file_get_contents(__DIR__ . '/stubs/certificateWithUppercaseDomains.json'),
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
            file_get_contents(__DIR__ . '/stubs/spatieCertificateFields.json'),
            true
        );

        $rawCertificateFieldsPreCertificate = json_decode(
            file_get_contents(__DIR__ . '/stubs/preCertificate.json'),
            true
        );

        $certificateNormal = new SslCertificate($rawCertificateFieldsNormalCertificate);
        $certificatePreCertificate = new SslCertificate($rawCertificateFieldsPreCertificate);

        $this->assertFalse($certificateNormal->isPreCertificate());
        $this->assertTrue($certificatePreCertificate->isPreCertificate());
    }

    /** @test */
    public function it_can_saved_to_and_created_from_an_array()
    {
        $certificate = SslCertificate::createForHostName('spatie.be');

        $certificateProperties = $certificate->toArray();

        $certificate = SslCertificate::createFromArray($certificateProperties);

        $this->assertTrue($certificate->appliesToUrl('spatie.be'));
    }
}
