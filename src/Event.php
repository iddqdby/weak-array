<?php

/*
 * The MIT License
 *
 * Copyright 2016 Sergey Protasevich.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace WeakArray;

use SplSubject;
use SplObserver;
use InvalidArgumentException;


/**
 * WeakArray event.
 */
class Event implements SplSubject {

    const TYPE_NOTIFY = 0;
    const TYPE_SET = 1;
    const TYPE_UNSET = 2;
    const TYPES = [
        self::TYPE_NOTIFY,
        self::TYPE_SET,
        self::TYPE_UNSET,
    ];

    private $subject;
    private $type;
    private $key;


    /**
     * Create new Event.
     *
     * @param WeakArray $subject related instance of WeakArray
     * @param int $type type of the event, one of Event::TYPE_* constants
     * @param null|int|string $key related key, or NULL, if no key is involved
     * @throws InvalidArgumentException if type is not valid,
     * or key is neither NUll nor int nor string
     */
    public function __construct( WeakArray $subject, $type, $key ) {

        if( !in_array( $type, self::TYPES, true ) ) {
            throw new InvalidArgumentException( 'Type must be one Event::TYPE_* constants.' );
        }
        if( null !== $key && !is_int( $key ) && !is_string( $key ) ) {
            throw new InvalidArgumentException( sprintf( 'Key must be NULL, or of type "int" or "string", "%s" given.', gettype( $key ) ) );
        }

        $this->subject = $subject;
        $this->type = $type;
        $this->key = $key;
    }


    /**
     * Get instance of WeakArray.
     *
     * @return WeakArray the instance of WeakArray
     */
    public function getSubject() {
        return $this->subject;
    }


    /**
     * Get type of event.
     *
     * One of Event::TYPE_* constants.
     *
     * @return int type of event
     */
    public function getType() {
        return $this->type;
    }


    /**
     * Get related key.
     *
     * @return null|int|string related key
     */
    public function getKey() {
        return $this->key;
    }


    /**
     * Get related object, if it still exists.
     *
     * @return null|object related object,
     * or NULL if object does not exist anymore
     */
    public function getValue() {
        return $this->subject[ $this->key ];
    }


    /**
     * Attach observer to the related instance of WeakArray.
     *
     * @param $observer SplObserver the observer
     * @see WeakArray::attach()
     */
    public function attach( SplObserver $observer ) {
        $this->subject->attach( $observer );
    }


    /**
     * Detach observer from the related instance of WeakArray.
     *
     * @param $observer SplObserver the observer
     * @see WeakArray::detach()
     */
    public function detach( SplObserver $observer ) {
        $this->subject->detach( $observer );
    }


    /**
     * Notify the related instance of WeakArray.
     *
     * @see WeakArray::notify()
     */
    public function notify() {
        $this->subject->notify();
    }

}
