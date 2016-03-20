<?php

require_once __DIR__.'/../vendor/autoload.php';

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

?>

EXPECTED OUTPUT:

Event 1, key foo
Event 1, key bar
Event 1, key baz
Event 2, key foo
Event 2, key bar
Event 2, key baz
