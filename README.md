Object hydrator
==================

[![Gitter](https://badges.gitter.im/kassko/object_hydrator.svg)](https://gitter.im/kassko/object_hydrator?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge)

[![Build Status](https://travis-ci.org/kassko/object-hydrator.svg)](https://travis-ci.org/kassko/object-hydrator)

*****************
## Summary

* [Introduction](#introduction)
* [Knowing about compatibility](#knowing-about-compatibility)
* [Seeing a brief overview of object hydrator capabilities](#seeing-a-brief-overview-of-object-hydrator-capabilities)
* [Getting started with object hydrator in a classical or non-Symfony application](#getting-started-with-object-hydrator-in-a-classical-or-non-symfony-application)
introction)
    - [Registering an object hydrator](#registering-an-object-hydrator)
    - [Getting an object hydrator](#getting-an-object-hydrator)
    - [Configuring an object hydrator](#configuring-an-object-hydrator)
    - [Knowing about all configuration options](#knowing-about-all-configuration-options)
* [Getting started with object hydrator in a Symfony application](#getting-started-with-object-hydrator-in-a-symfony-application)
    - [Installing in a Symfony application](#installing-in-a-symfony-application)
    - [Registering the bundle to kernel](#registering-the-bundle-to-kernel)
    - [Getting an object hydrator in a Symfony application](#getting-an-object-hydrator-in-a-symfony-application)
    - [Configuring object hydrator in a Symfony application](#configuring-object-hydrator-in-a-symfony-application)
    - [Knowing about all configuration options in Symfony application](#knowing-about-all-configuration-options-in-symfony-application)
* [Seeing more details of object hydrator capabilities](#seeing-more-details-of-object-hydrator-capabilities)
* [Contributing](#contributing)
    - [Submitting your code](#submitting-your-code)
    - [Running tests to check non regression in your code base](#running-tests-to-check-non-regression-in-your-code-base)
    - [Writing new tests to cover your new code](#writing-new-tests-to-cover-your-new-code)
* [Accessing object hydrator community](#accessing-object-hydrator-community)

*****************
## Introduction

An object hydrator for Php.
This library was created to facilite to create object models from complex data models.

Note that this library replaces kassko/data-mapper.
Differences are the followings:
* a more appropriate naming - `object hydrator`
* a library which works with php 7.4/8.0 - kassko/data-mapper works with php <= 7.3
* many more features to facilite to create object models

Here are the following main features:
* Hydration
    * hydrate objects from raw data
    * hydrate nested objects
    * hydrate properties inherited from classes or imported from traits
    * hydrate collections
    * hydrate polymorphic collections
* Data source
    * hydrate properties from specific data sources
    * hydrate lazily properties - only when its getter is called
    * hydrate properties from the join of multiples datasources
    * use expressions to create complex joins of datasources
* Configuration
    * control hydration behaviour through configurations
    * choose configurations formats among Doctrine annotations, yaml, php or provide your format
    * use multiples configuration formats - by objects
    * create candidates configurations and let the hydrator choose at runtime the good configuration
    * create configuration containing dynamic values - expressions/methods to be evaluated at runtime

*****************
## Knowing about compatibility

Mandatory dependencies
| Dependency | Version |
| -----------|---------|
| php | [7.4, 8.0] |
| symfony/config | [3.4, 6.0] |
| symfony/dependency-injection | [3.4, 6.0] |

Optional dependencies
| Dependency | Version | Usage |
| -----------|---------|-------|
| symfony/expression-language | [3.4, 6.0] | to use dynamic class metadata|
| symfony/yaml | [3.4, 6.0] | to store class metadata in yaml resources |

*****************
## Seeing a brief overview of object hydrator capabilities

### Hydrating
```php
```

### Fetching data source and hydrating
```php
```

### Using multiples configurations format
```php
```

```yaml
```

*****************
## Getting started with object hydrator in a classical or non-Symfony application

### Installing

```sh
$ composer require kassko/object-hydrator:^1.0
$ composer install
```

### Registering an object hydrator

If you use data source and their property loading feature, you need to "register" object hydrator.

At the beginning of your app or in your bootstrap application file, simply add the following
```php
(new \Big\Hydrator\HydratorBuilder())->build();
```

### Getting an object hydrator

```php
$hydrator = (new \Big\Hydrator\HydratorBuilder())->build();
```
When you get an object hydrator, you also register it.

### Configuring an object hydrator

* When registering
```php
(new \Big\Hydrator\HydratorBuilder())->config(['psr_container' => $myPsrContainer])->build();
```

* Or when getting (and so registering too)
```php
$hydrator = (new \Big\Hydrator\HydratorBuilder())->config(['psr_container' => $myPsrContainer])->build();
```

### Knowing about all configuration options

```php
[
    'class_metadata' => [
        'namespace' => [
            'annotations' => [
                'enabled' => false,
                'type' => 'doctrine',     # One of "native"; "doctrine"
            ],
            'file_resource' => [
                'enabled' => false,
                'type' => NULL,           # One of "php"; "yaml"; "json", Required
                'file_path' => NULL,      # Required
            ],
            'method_ressource' => [
                'enabled' => false,
                'type' => NULL,           # One of "php"; "yaml"; "json", Required
                'method_name' => NULL,    # Required
            ],
        ],
    ],
    'data_source_expressions' => [
        'keywords' => [
            'this_keyword' => '##this',
            'this_norm_keyword' => '##thisNorm',
            'variables_keyword' => '##variables',
        ],
        'markers' => [
            'property_marker' => '#',
            'direct_property_marker' => '!#',
            'service_marker' => '@',
            'source_marker' => '#source_',
            'source_tag_marker' => '#sourceTag_',
            'variable_marker' => '#variable_',
        ],
    ],
    'psr_container' => NULL,
    'service_provider' => NULL,
    'logger_key' => NULL,
]
```

*****************
## Getting started with object hydrator in a Symfony app

### Installing in Symfony application

```sh
$ composer require kassko/object-hydrator:^1.0
$ composer install
```

### Registering bundle to kernel

```php
Kassko\ObjectHydrator\FrameworkBridge\Symfony\KasskoObjectHydratorBundle
```

### Getting an object hydrator in a Symfony application

A service `Kassko\ObjectHydrator\Hydrator` is available.

### Configuring object hydrator in a Symfony application

```yaml
# in your object hydrator config yaml file
kassko_object_hydrator: ~
```

### Knowing about all configuration options in Symfony application

```yaml
kassko_object_hydrator:
    class_metadata:
        # Prototype
        namespace:
            annotations:
                enabled:              false
                type:                 doctrine # One of "native"; "doctrine"
            file_resource:
                enabled:              false
                type:                 ~ # One of "php"; "yaml"; "json", Required
                file_path:            ~ # Required
            method_ressource:
                enabled:              false
                type:                 ~ # One of "php"; "yaml"; "json", Required
                method_name:          ~ # Required
    data_source_expressions:
        keywords:
            this_keyword:         '##this'
            this_norm_keyword:    '##thisNorm'
            variables_keyword:    '##variables'
        markers:
            property_marker:      '#'
            direct_property_marker: '!#'
            service_marker:       '@'
            source_marker:        '#source_'
            source_tag_marker:    '#sourceTag_'
            variable_marker:      '#variable_'
    psr_container:        null
    service_provider:     null
    logger_key:           null
```

*****************
## Seeing more details of object hydrator capabilities
[detailed features]

[detailed features]: resources/documentation/detailed-features.md

*****************
## Contributing
====================

### Submitting your code

* naming your branch
* adding the good pull request label

### Running tests to check non regression in your code base
* check travis feed back on your pull request to know if your code do not break anything
* or run tests locally
```bash
./bin/phpunit -c tests/unit
./bin/phpunit -c tests/integration
./bin/phpunit -c framework-bridge/symfony/tests/integration
```

### Writing new tests to cover your new code

It is also recommended you write tests for features you add in `tests/unit` and `tests/integration`


## Accessing community

[object-hydrator community](https://gitter.im/kassko/object_hydrator?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge)
