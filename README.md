# WeakArray

## Short description

WeakArray is an array of weak references, based on WeakRef PHP extension (see https://pecl.php.net/package/Weakref).

It keeps weak references to objects, allowing them to be garbage-collected when there are no other references present.

## How to install

```sh
composer require iddqdby/weak-array
```

## Examples

### Basic usage

```php

$weak_array = new WeakArray();

$foo = new stdClass();
$bar = new stdClass();
$baz = new stdClass();

$weak_array['foo'] = $foo;
$weak_array['foo'] = $foo;

```

See `test/` directory for more examples.

## Requirements

PHP >= 5.5

## License

This program is licensed under the MIT License. See [LICENSE](LICENSE).
