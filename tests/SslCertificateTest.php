<?php

use Carbon\Carbon;

use function Spatie\Snapshots\assertMatchesJsonSnapshot;

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

    $expectedFields = json_decode(file_get_contents(__DIR__ . '/stubs/spatieCertificateFields.json'), true);

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

it('can determine the public key algorithm')
    ->expect(fn () => $this->certificate->getPublicKeyAlgorithm())
    ->toEqual("Unknown");

it('can determine the public key size')
    ->expect(fn () => $this->certificate->getPublicKeySize())
    ->toEqual(0);

    it('can determine the additional domains', function () {
    expect($this->certificate->getAdditionalDomains())->toHaveCount(3)
        ->and($this->certificate->getAdditionalDomains()[0])->toEqual('spatie.be')
        ->and($this->certificate->getAdditionalDomains()[1])->toEqual('www.spatie.be')
        ->and($this->certificate->getAdditionalDomains()[2])->toEqual('*.otherdomain.com');
});

it('can determine the valid from date')
    ->expect(fn () => $this->certificate->validFromDate())->toBeInstanceOf(Carbon::class)
    ->and(fn () => $this->certificate->validFromDate()->format('Y-m-d H:i:s'))->toEqual('2016-05-19 16:50:00');
;

it('can determine the lifespan in days')
    ->expect(fn () => $this->certificate->lifespanInDays())->toEqual(90);

it('can determine if the certificate is valid', function () {
    Carbon::setTestNow(Carbon::create('2016', '05', '19', '16', '45', '00', 'utc'));
    expect($this->certificate->isValid())->toBeFalse();

    Carbon::setTestNow(Carbon::create('2016', '05', '19', '16', '51', '00', 'utc'));
    expect($this->certificate->isValid())->toBeTrue();

    Carbon::setTestNow(Carbon::create('2016', '08', '17', '16', '49', '00', 'utc'));
    expect($this->certificate->isValid())->toBeTrue();

    Carbon::setTestNow(Carbon::create('2016', '08', '17', '16', '51', '00', 'utc'));
    expect($this->certificate->isValid())->toBeFalse();
});

it('can determine if the certificate is expired', function () {
    Carbon::setTestNow(Carbon::create('2016', '05', '19', '16', '45', '00', 'utc'));
    expect($this->certificate->isExpired())->toBeFalse();

    Carbon::setTestNow(Carbon::create('2016', '05', '19', '16', '51', '00', 'utc'));
    expect($this->certificate->isExpired())->toBeFalse();

    Carbon::setTestNow(Carbon::create('2016', '08', '17', '16', '49', '00', 'utc'));
    expect($this->certificate->isExpired())->toBeFalse();

    Carbon::setTestNow(Carbon::create('2016', '08', '17', '16', '51', '00', 'utc'));
    expect($this->certificate->isExpired())->toBeTrue();
});

it('can determine if the certificate is valid until a date', function () {
    // Expire date of certificate is: 17/08/2016 16:50
    Carbon::setTestNow(Carbon::create('2016', '08', '10', '16', '49', '00', 'utc'));     // 10/08   16:49
    expect($this->certificate->isValidUntil(Carbon::now()->addDays(2)))->toBeTrue();    // 12/08

    Carbon::setTestNow(Carbon::create('2016', '08', '10', '16', '49', '00', 'utc'));     // 10/08   16:49
    expect($this->certificate->isValidUntil(Carbon::now()->addDays(8)))->toBeFalse();    // 18/08

    Carbon::setTestNow(Carbon::create('2016', '08', '16', '16', '49', '00', 'utc'));     // 16/08   16:49
    expect($this->certificate->isValidUntil(Carbon::now()->addDays(1)))->toBeTrue();     // 17/08

    Carbon::setTestNow(Carbon::create('2016', '08', '16', '16', '51', '00', 'utc'));     // 16/08   16:51
    expect($this->certificate->isValidUntil(Carbon::now()->addDays(1)))->toBeFalse();      // 17/08

    Carbon::setTestNow(Carbon::create('2016', '08', '17', '16', '49', '00', 'utc'));     // 17/08   16:49
    expect($this->certificate->isValidUntil(Carbon::now()->addDays(1)))->toBeFalse();    // 18/08

    Carbon::setTestNow(Carbon::create('2016', '08', '17', '16', '51', '00', 'utc'));     // 17/08   16:51
    expect($this->certificate->isValidUntil(Carbon::now()->addDays(1)))->toBeFalse();    // 17/08
});

it('can determine if the certificate is valid for a certain domain', function () {
    expect($this->certificate->isValid('spatie.be'))->toBeTrue()
        ->and($this->certificate->isValid('www.spatie.be'))->toBeTrue()
        ->and($this->certificate->isValid('another.spatie.be'))->toBeFalse()
        ->and($this->certificate->isValid('www.another.spatie.be'))->toBeFalse()
        ->and($this->certificate->isValid('another.www.another.spatie.be'))->toBeFalse()
        ->and($this->certificate->isValid('otherdomain.com'))->toBeTrue()
        ->and($this->certificate->isValid('www.otherdomain.com'))->toBeTrue()
        ->and($this->certificate->isValid('another.otherdomain.com'))->toBeTrue()
        ->and($this->certificate->isValid('www.another.otherdomain.com'))->toBeFalse()
        ->and($this->certificate->isValid('another.www.another.otherdomain.com'))->toBeFalse()
        ->and($this->certificate->isValid('facebook.com'))->toBeFalse()
        ->and($this->certificate->isValid('spatie.be.facebook.com'))->toBeFalse()
        ->and($this->certificate->isValid('www.spatie.be.facebook.com'))->toBeFalse();
});

it('can create an instance for the given host', function () {
    $downloadedCertificate = SslCertificate::createForHostName('spatie.be');

    expect($downloadedCertificate->getDomain())->toBe('spatie.be');
});

it('provides a fluent interface to set all options', function () {
    $downloadedCertificate = SslCertificate::download()
        ->usingPort(443)
        ->setTimeout(30)
        ->forHost('spatie.be');

    expect($downloadedCertificate->getDomain())->toBe('spatie.be');
});

it('provides a fluent interface to set all options with hostport', function () {
    $downloadedCertificate = SslCertificate::download()
        ->setTimeout(30)
        ->forHost($this->domainWithDifferentPort . ':' . $this->differentPort);

    expect($downloadedCertificate->getDomain())->toBe($this->domainWithDifferentPort);
});

it('can convert the certificate to json', function () {
    assertMatchesJsonSnapshot($this->certificate->getRawCertificateFieldsJson());
});

it('can convert the certificate to a string', function () {
    expect((string) $this->certificate)->toEqual($this->certificate->getRawCertificateFieldsJson());
});

it('can get the hash of a certificate')
    ->expect(fn () => $this->certificate->getHash())
    ->toEqual('55353c8a63ab7669bb37a2692d2b0f3d');

it('can get all domains', function () {
    expect($this->certificate->getDomains())->toMatchArray([
        0 => 'spatie.be',
        1 => 'www.spatie.be',
        2 => '*.otherdomain.com',
    ]);
});

it('can get the days until the expiration date')
    ->expect(fn () => $this->certificate->daysUntilExpirationDate())
    ->toEqual(77);

it('can determine if it is self signed')
    ->expect(fn () => $this->certificate->isSelfSigned())
    ->toBeFalse();

it('can determine if it uses sha1 hasing')
    ->expect(fn () => $this->certificate->usesSha1Hash())
    ->toBeFalse();

it('can determine if the certificate has a certain domain', function () {
    expect([
        $this->certificate->containsDomain('spatie.be'),
        $this->certificate->containsDomain('www.spatie.be'),
    ])->each->toBeTrue();

    expect([
        $this->certificate->containsDomain('www.example.com'),
        $this->certificate->containsDomain('notreallyspatie.be'),
        $this->certificate->containsDomain('spatie.be.example.com'),
    ])->each->toBeFalse();
});

it('can be encoded as json', function () {
    $sslCertificate = Downloader::downloadCertificateFromUrl('spatie.be');

    $serializable = serialize($sslCertificate);

    expect(strlen($serializable))->toBeGreaterThan(1000);

    $sslCertificate = Downloader::downloadCertificateFromUrl('www.facebook.com');

    $serializable = serialize($sslCertificate);

    expect(strlen($serializable))->toBeGreaterThan(1000);

    $sslCertificate = Downloader::downloadCertificateFromUrl($this->domainWithDifferentPort . ':' . $this->differentPort);

    $serializable = serialize($sslCertificate);

    expect(strlen($serializable))->toBeGreaterThan(1000);
});

it('does not notify on wrong domains', function () {
    $rawCertificateFields = json_decode(
        file_get_contents(__DIR__ . '/stubs/certificateWithRandomWildcardDomains.json'),
        true
    );

    $this->certificate = new SslCertificate($rawCertificateFields);

    expect($this->certificate->appliesToUrl('https://coinmarketcap.com'))->toBeFalse();
});

it('correctly compares uppercase domain names', function () {
    $rawCertificateFields = json_decode(
        file_get_contents(__DIR__ . '/stubs/certificateWithUppercaseDomains.json'),
        true
    );

    $this->certificate = new SslCertificate($rawCertificateFields);

    expect($this->certificate->appliesToUrl('spatie.be'))->toBeTrue()
        ->and($this->certificate->appliesToUrl('www.spatie.be'))->toBeTrue();
});

it('correctly identifies pre certificates', function () {
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

    expect($certificateNormal->isPreCertificate())->toBeFalse()
        ->and($certificatePreCertificate->isPreCertificate())->toBeTrue();
});

it('can saved to and created from an array', function () {
    $certificate = SslCertificate::createForHostName('spatie.be');

    $certificateProperties = $certificate->toArray();

    $certificate = SslCertificate::createFromArray($certificateProperties);

    expect($certificate->appliesToUrl('spatie.be'))->toBeTrue();
});
