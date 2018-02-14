# Mutex implementation

## Current status
### General
[![Build Status](https://secure.travis-ci.org/nokitakaze/php-serializer.png?branch=master)](http://travis-ci.org/nokitakaze/php-serializer)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nokitakaze/php-serializer/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/nokitakaze/php-serializer/)
![Code Coverage](https://scrutinizer-ci.com/g/nokitakaze/php-serializer/badges/coverage.png?b=master)
<!-- [![Latest stable version](https://img.shields.io/packagist/v/nokitakaze/serializer.svg?style=flat-square)](https://packagist.org/packages/nokitakaze/serializer) -->

## Usage
At first
```bash
composer require nokitakaze/serializer
```

And then
```php
require_once 'vendor/autoload.php';
$mutex = new FileMutex([
    'name' => 'foobar',
]);
```
