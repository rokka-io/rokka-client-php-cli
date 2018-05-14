# Rokka PHP CLI


[![Build Status](https://api.travis-ci.org/rokka-io/rokka-client-php-cli.svg?branch=master)](https://travis-ci.org/rokka-io/rokka-client-php-cli)
[![StyleCI](https://styleci.io/repos/54209439/shield)](https://styleci.io/repos/54209439)
[![Latest Stable Version](https://poser.pugx.org/rokka/client-cli/version.png)](https://packagist.org/packages/rokka/client-cli)

A stand-alone console client for Rokka, build with Symfony components.

## Commands

Available commands:

 * `image:copy`: Copy the given image to another organization
 * `image:copy-all`: Copies all existing image between organizations
 * `image:delete`: Delete an image from a Rokka organization by its hash
 * `image:delete-all`: Delete all images from a Rokka organization
 * `image:download`: Download a source image from Rokka, given its hash
 * `image:info`: Given an image hash, output its details (name, filesize, metadatas)
 * `image:list`: List all uploaded images (includes offset, limit, sort and image-search options)
 * `image:render`: Render and download an image from Rokka given its hash
 * `image:restore`: Restore the given image.
 * `image:set-subjectarea`: Updates/adds the SubjectArea metadata to a given image
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

Default settings are loaded from a `rokka.yml` file, if it exists, from the current location.
Create a file with the following contents, and run `bin/rokka-cli`.

```
rokka_cli:
    api_key: your-api-key
    organization: organization-name
```

The commands `organization:create` and `user:create` have a `--save-as-default` option to create and save
the new values to the `rokka.yml` defaults file.

## Installation: Composer

 - `composer require rokka/client-cli`
 - run the CLI from `vendor/bin/rokka-cli`

## Installation: GIT

 - `git clone https://github.com/rokka-io/rokka-client-php-cli.git`
 - `cd rokka-client-php-cli && composer install`
 - run the CLI from `bin/rokka-cli`

# Building rokka-cli.phar

Rokka-CLI uses [Box](http://box-project.github.io/box2/) to build executable Phars.

 - Checkout the GIT repository
 - globally install the `box2` tool
 - run `compsoser install --no-dev` (install only the required libraries, excluding any development dependencies)
 - run `box build` in the project root to build `rokka-cli.phar`.

## Running PHP-CS-Fixer

```
curl http://cs.sensiolabs.org/download/php-cs-fixer-v2.phar > /tmp/php-cs-fixer.phar
php /tmp/php-cs-fixer.phar  fix -v --diff --using-cache=yes src/
```