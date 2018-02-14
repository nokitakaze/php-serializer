# Safe (un-)serialization of any data
[Remote code execution via PHP unserialize](https://www.notsosecure.com/remote-code-execution-via-php-unserialize/).
[Official documentation](http://php.net/manual/en/function.unserialize.php) says 
> DO NOT pass untrusted user input to unserialize() regardless of the options value of allowed_classes. Unserialization can result in code being loaded and executed due to object instantiation and autoloading, and a malicious user may be able to exploit this

But JSON does not implement data as PHP does. I.e. JSON does not support `[1=>2,3=>4,"a"=>5,"and"=>"so"]`.

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
$text = NokitaKaze\Serializer\Serializer::serialize($data);
$data = NokitaKaze\Serializer\Serializer::unserialize($text, $is_valid);
```
