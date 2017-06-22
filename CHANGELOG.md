# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.4.0 - TBD

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.3.0 - 2017-06-22

### Added

- [#70](https://github.com/zendframework/ZendService_Amazon/pull/70) added
  support for IT, ES, CN, BR amazon endpoints and added new $useHttps constructor
  parameter to `ZendService\Amazon\Amazon`

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.2.0 - 2017-03-15

### Added

- Nothing.

### Changed

- [#69](https://github.com/zendframework/ZendService_Amazon/pull/69) changed
  folder structure to PSR-4. No action is needed if you use composer to install
  package.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.1.0 - 2017-03-09

### Added

- [#63](https://github.com/zendframework/ZendService_Amazon/pull/63) added
  support for zend-crypt v3 and zend-json v3
- [#65](https://github.com/zendframework/ZendService_Amazon/pull/65) added php 7

### Deprecated

- Nothing.

### Removed

- [#65](https://github.com/zendframework/ZendService_Amazon/pull/65) dropped
  php <5.6 support

### Fixed

- [#32](https://github.com/zendframework/ZendService_Amazon/pull/48) added
  missing use statement
- [#49](https://github.com/zendframework/ZendService_Amazon/pull/49) fixed date
  format mismatch with signature in S3
- [#67](https://github.com/zendframework/ZendService_Amazon/pull/67) fixed
  off-by-one bug in S3 stream that was truncating and corrupting data

