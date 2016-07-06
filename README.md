# Rokka PHP CLI

A raw CLI for Rokka, build with Symfony3 components.

## Commands

Available commands:

 * `image:clone`: Copies an image between organizations
 * `image:clone-all`: Copies all existing image between organizations
 * `image:delete`: Delete an image from a Rokka organization by its hash
 * `image:delete-all`: Delete all images from a Rokka organization
 * `image:download`: Download a source image from Rokka, given its hash
 * `image:info`: Given an image hash, output its details (name, filesize, metadatas)    
 * `image:list`: List all uploaded images
 * `image:render`: Render and download an image from Rokka given its hash
 * `image:set-subjectarea`: Updates/adds the SubjectArea metadata to a given image
 * `image:upload`: Upload a given image file to Rokka.io
 
 * `stack:clone`: Copies a given ImageStack to another name (or to a different organization) 
 * `stack:clone-all`: Clones all ImageStack to another organization 
 * `stack:delete`: Removes an ImageStack
 * `stack:list`: List all available ImageStacks
 * `stack:info`: Given a Stack name, output its details (name, operations, options)

 * `organization:create`: Creates a new organization
 * `organization:info`: Prints the details of a given organization
 * `organization:membership:add`: Adds a membership given a user email and role
 * `organization:membership:info`: Prints the details of a given membership (by user email and organization)
    
 * `user:create`: Creates and register a new User on Rokka.io

Default settings are loaded from a `rokka.yml` file, if it exists, from the current location.
Create a file with the following contents, and run `bin/rokka-cli.php`.

```
rokka_cli:
    api_key: your-api-key
    api_secret: your-api-secret
    organization: organization-name
```

The commands `organization:create` and `user:create` have a `--save-as-default` option to create and save
the new values to the `rokka.yml` defaults file. 

## Building rokka-cli.phar

Rokka-CLI uses [Box](http://box-project.github.io/box2/) to build executable Phars.
Once _Box_ is installed, just run `box build` in the project root to build `rokka-cli.phar`.


