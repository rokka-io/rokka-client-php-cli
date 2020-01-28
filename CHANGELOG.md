# Change Log
All notable changes to this project will be documented in this file based on the
[Keep a Changelog](http://keepachangelog.com/) Standard.
This project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased](https://github.com/rokka-io/rokka-client-php-cli/compare/1.8.0...master)

### Added
### Changed
### Deprecated
### Fixed
### Removed
### Security

## 1.9.0 (unreleased)

### Changed
- Allow installation with Symfony 5, drop support for unmaintained versions of Symfony
- Drop support for PHP 5

## [1.8.0](https://github.com/rokka-io/rokka-client-php-cli/releases/tag/1.8.0) - 2018-12-13

### Changed
- `image:copy-all` uses the new batch copy of rokka. Makes copying images much faster

## [1.7.0](https://github.com/rokka-io/rokka-client-php-cli/releases/tag/1.7.0) - 2018-10-25

### Added
-  `organization:membership:add` takes now multiple roles, instead of just one

### Changed
- Adjusted to new rokka/client 1.7.0 membership methods
- Membership methods take a user_id instead of an email now (email still works, but is deprecated)

## [1.6.2](https://github.com/rokka-io/rokka-client-php-cli/releases/tag/1.6.2) - 2018-07-11
### Fixed
- Fixed installation of bin/rokka-cli into bin folder.

## [1.6.1](https://github.com/rokka-io/rokka-client-php-cli/releases/tag/1.6.1) - 2018-05-14
### Fixed
- Fixed stack:clone command. It was not working properly at all.

## [1.6.0](https://github.com/rokka-io/rokka-client-php-cli/releases/tag/1.6.0) - 2018-04-16
### Added
- Added image:restore
- Added image:copy
- Added image:copy-all
- Fixed image:delete-all
### Deprecated
- Deprecated image:clone
- Deprecated image:clone-all

## [1.5.1](https://github.com/rokka-io/rokka-client-php-cli/releases/tag/1.5.1) - 2018-03-15
### Fixed
- Fixed bug introduced in 1.5.0 that made the configuration not see the organization parameter in BC mode.

## [1.5.0](https://github.com/rokka-io/rokka-client-php-cli/releases/tag/1.5.0) - 2018-02-19
### Fixed
- Fixed an issue with stack lists.
### Removed
- Removed $apiSecret, but backwards compatibility is kept.

## [1.4.0](https://github.com/rokka-io/rokka-client-php-cli/releases/tag/1.4.0) - 2017-12-05
### Added
- Symfony 4 support

## [1.3.0](https://github.com/rokka-io/rokka-client-php-cli/releases/tag/1.3.0) - 2017-11-04
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
