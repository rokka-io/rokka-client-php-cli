# Change Log
All notable changes to this project will be documented in this file based on the
[Keep a Changelog](http://keepachangelog.com/) Standard.
This project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased](https://github.com/rokka-io/rokka-client-php-cli/compare/1.3.0...master)
### Added
- Symfony 4 support
### Changed
### Deprecated
### Fixed
### Removed
### Security

## [1.3.0](https://github.com/rokka-io/rokka-client-php-cli/releases/tag/1.3.0) - 2017-11.04
### Changed
- Depends on rokka/client ^1.0.0
- Removed PHP 5.5 support

## [1.2.1 - Murano](https://github.com/rokka-io/rokka-client-php-cli/releases/tag/1.2.1) - 2017-08-30
### Changed
- [PR-33](https://github.com/rokka-io/rokka-client-php-cli/pull/33): Relax version constraints of symfony components to also allow 2.7 and 2.8

## [1.2.0 - Venezia](https://github.com/rokka-io/rokka-client-php-cli/releases/tag/1.2.0) - 2017-08-29
### Added
- [PR-29](https://github.com/rokka-io/rokka-client-php-cli/pull/29): Prepare for API changes of Rokka Client PHP
- [PR-31](https://github.com/rokka-io/rokka-client-php-cli/pull/31): When no configuration is found, do not show commands that can not be executed
### Changed
- [PR-30](https://github.com/rokka-io/rokka-client-php-cli/pull/30): Implement reusable commands
### Fixed
- [PR-32](https://github.com/rokka-io/rokka-client-php-cli/pull/32): Apply fixes from StyleCI

## [1.1.0](https://github.com/rokka-io/rokka-client-php-cli/releases/tag/1.1.0) - 2017-04-12
### Added
- [PR-27](https://github.com/rokka-io/rokka-client-php-cli/pull/27): Add support for new Search options in source image listing
- [PR-16](https://github.com/rokka-io/rokka-client-php-cli/pull/16): Added `stack:create` command
### Changed
- [PR-28](https://github.com/rokka-io/rokka-client-php-cli/pull/28): Updated DynamicMetadata handling
- [PR-24](https://github.com/rokka-io/rokka-client-php-cli/pull/24): Refactor commands and cleanup
- [PR-18](https://github.com/rokka-io/rokka-client-php-cli/pull/18): Extract client creation into a client provider
- [PR-19](https://github.com/rokka-io/rokka-client-php-cli/pull/19): Use initialize method for common checks
