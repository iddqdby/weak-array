<?php

require_once __DIR__.'/../vendor/autoload.php';

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

?>

EXPECTED OUTPUT:

Object set, key foo
Object set, key bar
Object set, key baz
Object destructed, key foo
Object unset, key bar
Object unset, key baz
