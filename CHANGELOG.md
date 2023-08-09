# Changelog

All notable changes to `ssl-certificate` will be documented in this file

## 2.4.1 - 2023-08-07

### What's Changed

- Missing type by @roquie in https://github.com/spatie/ssl-certificate/pull/178
- Convert all tests to pest by @alexmanase in https://github.com/spatie/ssl-certificate/pull/180

### New Contributors

- @roquie made their first contribution in https://github.com/spatie/ssl-certificate/pull/178
- @alexmanase made their first contribution in https://github.com/spatie/ssl-certificate/pull/180

**Full Changelog**: https://github.com/spatie/ssl-certificate/compare/2.4.0...2.4.1

## 2.4.0 - 2022-10-28

### What's Changed

- add the ability to load from file a DER based certificate by @marco-introini in https://github.com/spatie/ssl-certificate/pull/177

### New Contributors

- @marco-introini made their first contribution in https://github.com/spatie/ssl-certificate/pull/177

**Full Changelog**: https://github.com/spatie/ssl-certificate/compare/2.3.0...2.4.0

## 2.3.0 - 2022-09-07

### What's Changed

- Add getSerialNumber() by @hexathos in https://github.com/spatie/ssl-certificate/pull/176

### New Contributors

- @hexathos made their first contribution in https://github.com/spatie/ssl-certificate/pull/176

**Full Changelog**: https://github.com/spatie/ssl-certificate/compare/2.2.0...2.3.0

## 2.2.0 - 2022-06-16

### What's Changed

- Update .gitattributes by @angeljqv in https://github.com/spatie/ssl-certificate/pull/171
- add IPv6 Support by @LordSimal in https://github.com/spatie/ssl-certificate/pull/174

### New Contributors

- @angeljqv made their first contribution in https://github.com/spatie/ssl-certificate/pull/171
- @LordSimal made their first contribution in https://github.com/spatie/ssl-certificate/pull/174

**Full Changelog**: https://github.com/spatie/ssl-certificate/compare/2.1.3...2.2.0

## 2.1.3 - 2022-06-06

### What's Changed

- Keep original unknown error message by @rudiedirkx in https://github.com/spatie/ssl-certificate/pull/170

### New Contributors

- @rudiedirkx made their first contribution in https://github.com/spatie/ssl-certificate/pull/170

**Full Changelog**: https://github.com/spatie/ssl-certificate/compare/2.1.2...2.1.3

## 2.1.2 - 2021-11-17

- Make compatible with PHP 8.2

**Full Changelog**: https://github.com/spatie/ssl-certificate/compare/2.1.1...2.1.2

## 2.1.1 - 2021-10-03

- improve URL validation

## 2.1.0 - 2021-08-10

- add `toArray` and `createFromArray`

## 2.0.3 - 2021-07-08

- add support for custom ports (#143)

## 2.0.2 - 2021-07-04

- support spatie/macroable v2(#142)

## 2.0.1 - 2021-03-30

- fix edge case when using serialized certificates

## 2.0.0 - 2021-03-30

- require PHP 8+
- drop support for PHP 7.x
- use PHP 8 syntax where possible

## 1.22.1 - 2021-02-15

- fix `getOrganization` method which always returned an empty result (#139)

## 1.22.0 - 2020-11-09

- allow set followLocation to be set to zero, ignoring redirect (#134)

## 1.21.1 - 2020-11-04

- add support for PHP 8.0
- drop support for PHP 7.3 and below

## 1.21.0 - 2020-10-19

- add option to disable certificate validation in createForHostName (#132)

## 1.20.0 - 2020-10-07

- add `withSocketContextOptions`

## 1.19.1 - 2020-04-01

- `createFromFile` static method bugfix #127

## 1.19.0 - 2020-03-21

- add lifespan in days

## 1.18.0 - 2020-03-18

- get details from a certificate from string (#123)

## 1.17.2 - 2020-01-26

- throw exception instead of displaying warning when download fails

## 1.17.1 - 2020-01-26

- add missing exception for invalid IP address (#121)

## 1.17.0 - 2020-01-26

- add function to get certificate for hostname from IP address (#119)

## 1.16.1 - 2019-11-18

- set SSL option param 'peer_name' with hostname value (#113)

## 1.16.0 - 2019-09-30

- expose the remote address that served the certificates in the downloader (#110)

## 1.15.0 - 2019-07-22

- internals cleanup
- drop support for PHP 7.1 and below

## 1.14.1 - 2019-07-22

- add check for missing 'INTL_IDNA_VARIANT_UTS46' constant

## 1.14.0 - 2019-07-04

- add fingerprint sha256

## 1.13.1 - 2019-05-23

- Fix SSL check for IP addresses that serve a valid SSL, i.e. 1.1.1.1

## 1.13.0 - 2019-02-19

- add specific exceptions

## 1.12.11 - 2018-12-06

- add support for detecting pre-certificates

## 1.12.10 - 2018-12-03

- take into account that IDN functions in PHP are limited to 61 characters

## 1.12.9 - 2018-10-18

- allow Carbon v2

## 1.12.8 - 2018-06-24

- `idn_to_ascii` is not required anymore

## 1.12.7 - 2018-05-11

- use port 443 by default

## 1.12.6 - 2018-04-30

- normalize all hostnames to lowercase when checking validity

## 1.12.5 - 2018-04-24

- fix `appliesToUrl`

## 1.12.4 - 2018-03-02

- add `ext-intl` dep to `composer.json`

## 1.12.3 - 2018-02-26

- convert IDN to ASCII before attempting to validate

## 1.12.2 - 2018-02-05

- fix bug where wildcard matching could be wrong

## 1.12.1 - 2018-02-02

- close socket connection in `Downloader::fetchCertificates()`

## 1.12.0 - 2017-12-28

- add `getFingerprint()`

## 1.11.7 - 2017-11-08

- fixes for `containsDomain`: add literal and wildcard match for domains

## 1.11.6 - 2017-11-01

- fix `getDomains`

## 1.11.5 - 2017-10-30

- fix `getDomains`

## 1.11.4 - 2017-10-30

- fix `getDomain`

## 1.11.3 - 2017-10-25

- fix keys of array with all domain names

## 1.11.2 - 2017-10-25

- only return unique domain names

## 1.11.1 - 2017-10-23

- fix dependencies

## 1.11.0 - 2017-09-16

- make `SslCertificate` macroable

## 1.10.0 - 2017-09-15

- add `containsDomain`

## 1.9.1 - 2017-09-04

- avoid error when issuer is empty

## 1.9.0 - 2017-08-25

- add `usesSha1Hash`

## 1.8.0 - 2017-08-25

- add `isSelfSigned`

## 1.7.0 - 2017-08-25

- add `getDaysUntilExpirationDate`

## 1.6.0 - 2017-08-23

- add `getDomains`

## 1.5.0 - 2017-08-11

- add `withVerifyPeer` and `withVerifyPeerName` methods on Downloader.
- add `getRawCertificateFieldsJson`, `getHash` and `__toString` methods on `SslCertificate`
- fixes bug where a peer certificate appears twice in a chain

## 1.4.0 - 2017-08-10

- add `getCertificates`, `usingSni`, `withFullChain` methods on Downloader.

## 1.3.2 - 2017-07-18

- fix wildcard matching

## 1.3.1 - 2017-03-08

- fix `isValidUntil`

## 1.3.0 - 2016-12-17

- add fluent interface to download certificates

## 1.2.1 - 2016-11-15

- lower required version of Carbon

## 1.2.0 - 2016-08-20

- added `getSignatureAlgorithm`

## 1.1.0 - 2016-07-29

- added `isValidUntil`

## 1.0.0 - 2016-07-28

- initial release
