# Roave Security Advisories

This package ensures that your application doesn't have installed dependencies with known security vulnerabilities.

## Installation

```sh
~$ composer require roave/security-advisories:dev-master@DEV
```

## Stability

This package can only be required as `dev-master@DEV`: there will never be stable/tagged versions because of the 
nature of the problem being targeted. Security issues are a moving target, and locking your project to a specific 
tagged version of the package would not make any sense.

This package is therefore only suited for installation in the root of your deployable project.
