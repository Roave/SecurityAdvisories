# Roave Security Advisories

[![Build Status](https://travis-ci.org/Roave/SecurityAdvisories.svg?branch=master)](https://travis-ci.org/Roave/SecurityAdvisories)
[![Downloads](https://img.shields.io/packagist/dt/roave/security-advisories.svg)](https://packagist.org/packages/roave/security-advisories)

This package ensures that your application doesn't have installed dependencies with known security vulnerabilities.

## Installation

```sh
composer require --dev roave/security-advisories:dev-master
```

## Usage

This package does not provide any API or usable classes: its only purpose is to prevent installation of software
with known and documented security issues.
Simply add `"roave/security-advisories": "dev-master"` to your `composer.json` `"require-dev"` section and you will
not be able to harm yourself with software with known security vulnerabilities.

For example, try following:

```sh
composer require --dev roave/security-advisories:dev-master
# following commands will fail:
composer require symfony/symfony:2.5.2
composer require zendframework/zendframework:2.3.1 
```

The checks are only executed when adding a new dependency via `composer require` or when running `composer update`:
deploying an application with a valid `composer.lock` and via `composer install` won't trigger any security versions
checking.

## Support

[Professionally supported `roave/security-advisories` is available through Tidelift](https://tidelift.com/subscription/pkg/packagist-roave-security-advisories?utm_source=packagist-roave-security-advisories&utm_medium=referral&utm_campaign=readme).

You can also contact us at team@roave.com for looking into security issues in your own project.

## Stability

This package can only be required in its `dev-master` version: there will never be stable/tagged versions because of
the nature of the problem being targeted. Security issues are in fact a moving target, and locking your project to a 
specific tagged version of the package would not make any sense.

This package is therefore only suited for installation in the root of your deployable project.

## Sources

This package extracts information about existing security issues in various composer projects from 
the [FriendsOfPHP/security-advisories](https://github.com/FriendsOfPHP/security-advisories) repository.
