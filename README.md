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
        // see WeakArray\Event class to view all available methods and Event::TYPE_* constants
        printf("Event %s, key %s\n", $event->getType(), $event->getKey());
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

unset($weak_array['foo']);
unset($weak_array['bar']);
unset($weak_array['baz']);
```

#### Output

```
Event 1, key foo
Event 1, key bar
Event 1, key baz
Event 2, key foo
Event 2, key bar
Event 2, key baz
```

It is also possible to detect destruction of stored objects by garbage collector:

#### Code

```php
class Observer implements \SplObserver {
    public function update(\SplSubject $event) {
        if(WeakArray\Event::TYPE_DESTRUCT == $event->getType()) {
          printf("Object %s destroyed by garbage collector.\n", $event->getKey());
        }
    }
}

// Set second optional parameter to true to enable detection of destruction;
// see WeakArray\WeakArray::__construct() to view all available parameters
$weak_array = new WeakArray\WeakArray([], true);
$observer = new Observer();

$weak_array->attach($observer);

$foo = new stdClass();
$weak_array['foo'] = $foo;

// ... do something ...

unset($foo);

// ... do something more ...

// Note that garbage collection is less intensive (not immediate)
// when detection of object destructions is enabled;
// you can make it more intensive (paying for it with some performance)
// by adjusting third argument of WeakArray constructor
// (see WeakArray\WeakArray::__construct()).
```

#### Output

```
Object foo destroyed by garbage collector.
```

See `test/` directory for more examples.

## Requirements

* PHP >= 5.5
* WeakRef PHP extension >= 0.3

## License

This program is licensed under the MIT License. See [LICENSE](LICENSE).
