# Idlab Helper Bundle

This bundle provides helper methods which we use in most of our projects. 
These helpers can format phone numbers or check EAN validity, sanitize a string or remove accents, you name it.
Check both `src/Service/Helper.php` and `src/SetaticHelper.php` to see all available helpers

INFO : `src/Service/Helper.php` is intended for helper who need the container and support injection. Right now there are not such helpers


Installation
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
$ composer require idlab/idlab/helper-bundle
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require idlab/idlab/helper-bundle
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Idlab\ComposerChangelogBundle\IdlabHelperBundle::class => ['all' => true],
];
```

## Usage example

```php
use \Idlab\HelperBundle\StaticHelper;

//...

$phone = StaticHelper::cleanPhoneNumber($uglyPhoneNumber);
```

Will transform `0041-22 621.12.32` to `+41 22 621 12 32` 

## Configuration

Right now there is no useful configuration.
If the file has not yet been generated, create a new `config/packages/idlab_helper.yaml` file.
The defaulf values are

```yaml
idlab_helper:
    # future parameters go here...
```

NB: to see the configuration reference, run `$ php bin/console config:dump idlab_helper`

## Contribute

Please always run CS fixer before sumbitting a merge request (PHP CS Fixer lives in the `./vendors` directory)

```console
$ php ./vendor/bin/php-cs-fixer fix
```

