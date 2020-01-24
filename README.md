# A class to validate SSL certificates

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/ssl-certificate.svg?style=flat-square)](https://packagist.org/packages/spatie/ssl-certificate)
[![MIT Licensed](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
![GitHub Workflow Status](https://img.shields.io/github/workflow/status/spatie/ssl-certificate/run-tests?label=tests)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/ssl-certificate.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/ssl-certificate)
[![StyleCI](https://styleci.io/repos/64165510/shield)](https://styleci.io/repos/64165510)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/ssl-certificate.svg?style=flat-square)](https://packagist.org/packages/spatie/ssl-certificate)

The class provided by this package makes it incredibly easy to query the properties on an ssl certificate. Here's an example:

```php
use Spatie\SslCertificate\SslCertificate;

$certificate = SslCertificate::createForHostName('spatie.be');

$certificate->getIssuer(); // returns "Let's Encrypt Authority X3"
$certificate->isValid(); // returns true if the certificate is currently valid
$certificate->expirationDate(); // returns an instance of Carbon
$certificate->expirationDate()->diffInDays(); // returns an int
$certificate->getSignatureAlgorithm(); // returns a string
```

Spatie is a webdesign agency based in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

## Installation

You can install the package via composer:

```bash
composer require spatie/ssl-certificate
```

## Important notice

Currently this package [does not check](https://github.com/spatie/ssl-certificate/blob/master/src/SslCertificate.php#L63-L74) if the certificate is signed by a trusted authority. We'll add this check soon in a next point release.

## Usage

You can create an instance of `Spatie\SslCertificate\SslCertificate` with this named constructor:

```php
$certificate = SslCertificate::createForHostName('spatie.be');
```

You can use this fluent style to specify a specific port to connect to.

```php
SslCertificate::download()
   ->usingPort($customPort)
   ->forHost($hostName);
```

You can also check the certificate on a different IP address using the same style.
```php
SslCertificate::download()
   ->fromIpAddress($ipAddress)
   ->forHost($hostName);
```

If the given `ipAddress` is invalid `Spatie\SslCertificate\Exceptions\InvalidIpAddress` will be thrown.

If the given `hostName` is invalid `Spatie\SslCertificate\Exceptions\InvalidUrl` will be thrown.

If the given `hostName` is valid but there was a problem downloading the certifcate `Spatie\SslCertificate\Exceptions\CouldNotDownloadCertificate` will be thrown.

### Getting the issuer name

```php
$certificate->getIssuer(); // returns "Let's Encrypt Authority X3"
```

### Getting the domain name

Returns the primary domain name for the certificate

```php
$certificate->getDomain(); // returns "spatie.be"
```

### Getting the certificate's signing algorithm

Returns the algorithm used for signing the certificate

```php
$certificate->getSignatureAlgorithm(); // returns "RSA-SHA256"
```

### Getting the additional domain names

A certificate can cover multiple (sub)domains. Here's how to get them.

```php
$certificate->getAdditionalDomains(); // returns ["spatie.be", "www.spatie.be]
```

A domain name return with this method can start with `*` meaning it is valid for all subdomains of that domain.

### Getting the fingerprint

```php
$certificate->getFingerprint(); // returns a fingerprint for the certificate
```

### Getting the SHA256 fingerprint

```php
$certificate->getFingerprintSha256(); // returns a SHA256 fingerprint for the certificate
```

### Getting the date when the certificate becomes valid

```php
$certificate->validFromDate(); // returns an instance of Carbon
```

### Getting the expiration date

```php
$certificate->expirationDate(); // returns an instance of Carbon
```



### Determining if the certificate is still valid

Returns true if the current Date and time is between `validFromDate` and `expirationDate`.

```php
$certificate->isValid(); // returns a boolean
```

You also use this method to determine if a given domain is covered by the certificate. Of course it'll keep checking if the current Date and time is between `validFromDate` and `expirationDate`.

```php
$certificate->isValid('spatie.be'); // returns true;
$certificate->isValid('laravel.com'); // returns false;
```

### Determining if the certificate is still valid until a given date

Returns true if the certificate is valid and if the `expirationDate` is after the given date.

```php
$certificate->isValidUntil(Carbon::now()->addDays(7)); // returns a boolean
```

### Determining if the certificate is expired

```php
$certificate->isExpired(); // returns a boolean if expired
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email freek@spatie.be instead of using the issue tracker.

## Postcardware

You're free to use this package, but if it makes it to your production environment we highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.

Our address is: Spatie, Samberstraat 69D, 2060 Antwerp, Belgium.

We publish all received postcards [on our company website](https://spatie.be/en/opensource/postcards).

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

The helper functions and tests were copied from the [Laravel Framework](https://github.com/laravel/framework).

## Support us

Spatie is a webdesign agency based in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

Does your business depend on our contributions? Reach out and support us on [Patreon](https://www.patreon.com/spatie).
All pledges will be dedicated to allocating workforce on maintenance and new awesome stuff.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
