# Roave Security Advisories

[![Hourly build](https://github.com/Roave/SecurityAdvisoriesBuilder/workflows/Hourly%20build/badge.svg?branch=latest)](https://github.com/Roave/SecurityAdvisoriesBuilder/actions?query=workflow%3A%22Hourly+build%22)
[![Downloads](https://img.shields.io/packagist/dt/roave/security-advisories.svg)](https://packagist.org/packages/roave/security-advisories)

This package ensures that your application doesn't have installed dependencies with known security vulnerabilities.

## Installation

Add `"roave/security-advisories": "dev-latest"` to your `composer.json` `"require-dev"` section and you will
not be able to harm yourself with software with known security vulnerabilities.

```sh
composer require --dev roave/security-advisories:dev-latest
```

## Usage

This package does not provide any API or usable classes: its only purpose is to prevent installation of software
with known and documented security issues. It provides a continuously updated
[list of these packages](composer.json),
causing Composer to show conflicts whenever a undesirable package version is to be installed.

The checks are executed automatically when adding a new dependency via `composer require`
or when running `composer update`.

Deploying an application with a valid `composer.lock` and via `composer install` won't trigger
any security versions checking.

For example, try the following:

```sh
composer require --dev roave/security-advisories:dev-latest
# following commands will fail due to a conflict with roave/security-advisories:
composer require symfony/symfony:2.5.2
composer require zendframework/zendframework:2.3.1 
```

### Manual checks

You may manually trigger a version check only by using the `--dry-run` flag on an update.

This requires Composer version 2 however, due to a wont-fix issue in deprecated Composer 1.x.

```sh
composer update --dry-run roave/security-advisories
```

Conflicts with existing packages cause Composer to return a failure exit code.

## roave/security-advisories for enterprise

Available as part of the Tidelift Subscription.

The maintainers of roave/security-advisories and thousands of other packages are working with Tidelift to deliver commercial support and maintenance for the open source dependencies you use to build your applications. Save time, reduce risk, and improve code health, while paying the maintainers of the exact dependencies you use. [Learn more](https://tidelift.com/subscription/pkg/packagist-roave-security-advisories?utm_source=packagist-roave-security-advisories&utm_medium=referral&utm_campaign=enterprise&utm_term=repo).

You can also contact us at team@roave.com for looking into security issues in your own project.

## Stability

This package can only be required in its `dev-latest` version: there will never be stable/tagged versions because of
the nature of the problem being targeted. Security issues are in fact a moving target, and locking your project to a 
specific tagged version of the package would not make any sense.

This package is therefore only suited for installation in the root of your deployable project.

## Sources

This package extracts information about existing security issues in various composer projects from 
the [FriendsOfPHP/security-advisories](https://github.com/FriendsOfPHP/security-advisories) repository and the [GitHub Advisory Database](https://github.com/advisories?query=ecosystem%3Acomposer).
