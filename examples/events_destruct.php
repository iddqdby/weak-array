<?php

require_once __DIR__.'/../vendor/autoload.php';

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

unset($foo);

?>

EXPECTED OUTPUT:

Object foo destroyed by garbage collector.
