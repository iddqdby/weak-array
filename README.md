# WeakArray

[![Build Status](https://travis-ci.org/iddqdby/weak-array.svg?branch=master)](https://travis-ci.org/iddqdby/weak-array)
[![Latest Stable Version](https://poser.pugx.org/iddqdby/weak-array/v/stable)](https://packagist.org/packages/iddqdby/weak-array)
[![Total Downloads](https://poser.pugx.org/iddqdby/weak-array/downloads)](https://packagist.org/packages/iddqdby/weak-array)
[![License](https://poser.pugx.org/iddqdby/weak-array/license)](https://packagist.org/packages/iddqdby/weak-array)

## Short description

WeakArray is an array of weak references, based on WeakRef PHP extension (see https://pecl.php.net/package/Weakref).

It keeps weak references to objects, allowing them to be garbage-collected when there are no other references present.

## How to install

```sh
composer require iddqdby/weak-array
```

or download the archive, extract it and include file `autoload.php`.

## Examples

### Basic usage

#### Code

```php
$weak_array = new WeakArray\WeakArray();

$foo = new stdClass();
$bar = new stdClass();
$baz = new stdClass();

$weak_array['foo'] = $foo;
$weak_array['bar'] = $bar;
$weak_array['baz'] = $baz;

var_export($weak_array['foo']);
echo "\n";

var_export($weak_array['bar']);
echo "\n";

var_export($weak_array['baz']);
echo "\n";

echo "====\n";

unset($foo);
unset($bar);

var_export($weak_array['foo']);
echo "\n";

var_export($weak_array['bar']);
echo "\n";

var_export($weak_array['baz']);
echo "\n";
```

#### Output

```
stdClass::__set_state(array(
))
stdClass::__set_state(array(
))
stdClass::__set_state(array(
))
====
NULL
NULL
stdClass::__set_state(array(
))
```

### Events

#### Code

```php
class Observer implements \SplObserver {
    public function update(\SplSubject $event) {
        // $event instanceof WeakArray\Event;
        // see WeakArray\Event class to view all available methods and Event::* constants
        switch ($event->getType()) {
            case WeakArray\Event::OBJECT_SET:
                $event_str = 'set';
                break;
            case WeakArray\Event::OBJECT_UNSET:
                $event_str = 'unset';
                break;
            case WeakArray\Event::OBJECT_DESTRUCTED:
                $event_str = 'destructed';
                break;
        }
        printf("Object %s, key %s\n", $event_str, $event->getKey());
    }
}

$weak_array = new WeakArray\WeakArray();
$observer = new Observer();

$weak_array->attach($observer);

$foo = new stdClass();
$bar = new stdClass();
$baz = new stdClass();

$weak_array['foo'] = $foo;
$weak_array['bar'] = $bar;
$weak_array['baz'] = $baz;

unset($foo);

unset($weak_array['bar']);
unset($weak_array['baz']);
```

#### Output

```
Object set, key foo
Object set, key bar
Object set, key baz
Object destructed, key foo
Object unset, key bar
Object unset, key baz
```

See `examples/` and `test/` directories for working examples.

## Requirements

* PHP: 5.5, 5.6, 7.0
* WeakRef PHP extension: 0.2.6 for PHP 5.5 and PHP 5.6, >=0.3 for PHP 7.0

## Homepage

https://iddqdby.github.io/weak-array/

## Versioning

This project follows the [Semantic Versioning](http://semver.org/) principles.

## License

This project is licensed under the MIT License. See [LICENSE](LICENSE).
