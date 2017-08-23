# Changelog

All notable changes to `ssl-certificate` will be documented in this file

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
