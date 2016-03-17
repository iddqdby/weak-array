<?php

use WeakArray\Event;


class WeakArrayTest_Observer implements SplObserver {

    private $collected = [];


    public function update( SplSubject $event ) {
        $this->collected[] = [
            'subject' => $event->getSubject(),
            'type' => $event->getType(),
            'key' => $event->getKey()
        ];
    }


    public function collected() {
        return $this->collected;
    }

}
