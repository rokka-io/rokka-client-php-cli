# Rokka PHP CLI


[![Build Status](https://api.travis-ci.org/rokka-io/rokka-client-php-cli.svg?branch=master)](https://travis-ci.org/rokka-io/rokka-client-php-cli)
[![StyleCI](https://styleci.io/repos/54209439/shield)](https://styleci.io/repos/54209439)
[![Latest Stable Version](https://poser.pugx.org/rokka/client-cli/version.png)](https://packagist.org/packages/rokka/client-cli)

A stand-alone console client for [rokka.io](https://rokka.io), built with Symfony components.

## Download: .phar

The cli is provided as a stand alone tool. Download the .phar from our [releases](https://github.com/rokka-io/rokka-client-php-cli/releases)
page and put it into e.g. `/usr/local/bin/rokka-cli`.

## Installation: Composer

Note: If you are using Symfony, use the [RokkaClientBundle](https://github.com/rokka-io/rokka-client-bundle/) which
provides these commands in a Symfony application.

 - `composer require rokka/client-cli`
 - run the CLI from `vendor/bin/rokka-cli`

## Commands

Available commands:

 * `image:copy`: Copy the given image to another organization
 * `image:copy-all`: Copies all existing image between organizations
 * `image:delete`: Delete an image from a Rokka organization by its hash
 * `image:delete-all`: Delete all images from a Rokka organization
 * `image:delete-subjectarea`: Remove the subject area metadata from an image
 * `image:download`: Download a source image from Rokka, given its hash
 * `image:info`: Given an image hash, output its details (name, filesize, metadatas)
 * `image:list`: List all uploaded images (includes offset, limit, sort and image-search options)
 * `image:render`: Render an image with a specified stack
 * `image:restore`: Restore the given image
 * `image:set-subjectarea`: Set the SubjectArea metadata to a given image
 * `image:upload`: Upload a given image file to Rokka.io

 * `stack:create`: Create a stack
 * `stack:clone`: Copies a given ImageStack to another name (or to a different organization)
 * `stack:clone-all`: Clones all ImageStack to another organization
 * `stack:list`: List all available ImageStacks
 * `stack:info`: Given a Stack name, output its details (name, operations, options)
 * `stack:delete`: Removes an ImageStack

 * `organization:create`: Creates a new organization
 * `organization:info`: Prints the details of a given organization
 * `organization:membership:add`: Adds a membership given a user email and role
 * `organization:membership:info`: Prints the details of a given membership (by user email and organization)

 * `user:create`: Creates and register a new User on Rokka.io

## Configuration

Without configuration, the command will only list the operations that are possible when not logged in.
Use the `organization:create` and `user:create` commands with the `--save-as-default` option to initialize
the configuration with your user.

If you have an existing account, create the file `rokka.yml` with the following content:

```
rokka_cli:
    api_key: your-api-key
    organization: organization-name
```

`rokka-cli` looks for the configuration file in the current working directory.

# Development

## Building rokka-cli.phar

Rokka-CLI uses [Box](https://github.com/humbug/box/) to build executable Phars.

 - Checkout the GIT repository
 - Run `make dist`
 - The .phar should be at dist/rokka-cli.phar

## Running PHP-CS-Fixer

```
curl http://cs.sensiolabs.org/download/php-cs-fixer-v2.phar > /tmp/php-cs-fixer.phar
php /tmp/php-cs-fixer.phar  fix -v --diff --using-cache=yes src/
```
