# Tokenization

A convenient PHP package for consuming Tokenization APIs

## Installation

The fastest way to get up and running is to install via [composer](https://getcomposer.org/), make sure you add the repository to
your composer.json file before you require the package:

```bash
$ composer require dennislindsey/tokenize
```

## Laravel/Lumen

There is a service provider included for integration with the Laravel framework. This provider will publish the proper configuration
files to their appropriate locations within the framework.

##### Laravel
To register the service provider in a Laravel project, add the following to the providers
array in `config/app.php`:

```php
'DennisLindsey\Tokenize\Providers\TokenizationServiceProvider',
```

##### Lumen
To register the service provider in a Lumen project, add the following to the providers
array in `bootstrap/app.php`:

```php
$app->register('DennisLindsey\Tokenize\Providers\TokenizationServiceProvider');
```

Now, when you execulte Laravel's `vendor:publish` Artisan command, the configuration files will be published to
`config/tokenization.php`.

```bash
$ php artisan vendor:publish --provider="DennisLindsey\Tokenize\Providers\TokenizationServiceProvider"
```

Alternatively, you could simply copy-paste `/path/to/your/vendor/directory/dennislindsey/tokenize/config/tokenization.php` to
`config/tokenization.php` to achieve the same effect.

_Note: you may need to install `basicit/lumen-vendor-publish` if your Laravel/Lumen installation does not support the
`vendor:publish` artisan command._

## Usage

### Tokenization

#### Initialize your tokenizer

```php
require __DIR__ . '/vendor/autoload.php';
use DennisLindsey\Tokenize\Repositories\TokenizeRepository as Tokenizer;

$tokenizer = new Tokenizer('TokenEx');
```

#### Create a token

```php
$token = $tokenizer->store("This is random data");
```

#### Validate a token

```php
$tokenizer->validate($token); // true or false
```

#### Get tokenized data

```php
$data = $tokenizer->get($token); // original data sent to the store() method
```

#### Delete a token

```php
$tokenizer->delete($token); // true or false
```

#### Errors and References

Each action call will return a reference ID that can be used to lookup a call in the provider (TokenEx) dashboard. Unsuccessful calls will also return an error describing the problem. Each can be accessed via:

```php
var_dump($tokenizer->getErrors()); // array, empty if no errors
var_dump($tokenizer->getReferenceNumber()); // string
```

## Notes

This library is inspired by the work done by **cliffom** (https://github.com/cliffom/tokenex-php).

## License

All code is open source under the terms of the [GNU GPL License](GNU GPL License)
