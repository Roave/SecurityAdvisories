# Roave Security Advisories

This package ensures that your application doesn't have installed dependencies with known security vulnerabilities.

## Installation

```sh
~$ composer require roave/security-advisories:dev-master@DEV
```

## Usage

This package does not provide any API or usable classes: its only purpose is to prevent installation of software
with known and documented security issues.
Simply add `"roave/security-advisories": "dev-master@DEV"` to your `composer.json` `"require"` section and you will
not be able to harm yourself with software with known security vulnerabilities.

## Stability

This package can only be required as `dev-master@DEV`: there will never be stable/tagged versions because of the 
nature of the problem being targeted. Security issues are a moving target, and locking your project to a specific 
tagged version of the package would not make any sense.

This package is therefore only suited for installation in the root of your deployable project.

## Sources

This package extracts information about existing security issues in various composer projects from 
the [FriendsOfPHP/security-advisories](https://github.com/FriendsOfPHP/security-advisories) repository.
