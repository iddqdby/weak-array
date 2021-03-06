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

use Iterator;
use Countable;
use ArrayAccess;
use SplObjectStorage;
use SplSubject;
use SplObserver;
use WeakRef;
use WeakMap;
use InvalidArgumentException;


/**
 * Array of weak references.
 */
class WeakArray implements Countable, ArrayAccess, Iterator, SplSubject {

    /** Default amout of interactions with instance of WeakArray before enforcing internal garbage collection */
    const GARBAGE_COLLECTION_PERIOD_DEFAULT = 1024;

    /** Minimal amout of interactions with instance of WeakArray before enforcing internal garbage collection */
    const GARBAGE_COLLECTION_PERIOD_INTENSIVE = 1;


    /** @var int */
    protected $gc_period;

    /** @var int */
    protected $gc_int_count = 0;

    /** @var WeakRef[] */
    protected $array = [];

    /** @var WeakMap<object, array<DestructionDetector>> */
    protected $destruction_detectors;

    /** @var SplObjectStorage */
    protected $observers;


    /**
     * Create new array of weak references.
     *
     * @param array $objects an optional array of objects
     * @param int $gc_period amout of interactions with instance of WeakArray
     * before enforcing internal garbage collection of empty references
     * (optional, default is WeakArray::GARBAGE_COLLECTION_PERIOD_DEFAULT)
     * @throws InvalidArgumentException if garbage collection period is less than 1,
     * or array contains value that is not an object
     */
    public function __construct( array $objects = [], $gc_period = self::GARBAGE_COLLECTION_PERIOD_DEFAULT ) {
        $this->observers = new SplObjectStorage();
        $this->destruction_detectors = new WeakMap();
        $this->setGarbageCollectionPeriod( $gc_period );
        foreach( $objects as $key => $obj ) {
            $this[ $key ] = $obj;
        }
    }


    protected function gc( $force = false ) {
        if( $force || ++$this->gc_int_count >= $this->gc_period ) {
            $this->gc_int_count = 0;
            // "array_filter()" resets internal array pointer, so we don't use it;
            // internal array pointer is used in implemented methods of the "Iterator" interface
            foreach( array_keys( $this->array ) as $key ) {
                if( !$this->array[ $key ]->valid() ) {
                    unset( $this->array[ $key ] );
                }
            }
        }
    }


    /**
     * Get keys of existing objects.
     *
     * @return array keys of existing objects
     */
    public function keys() {
        $this->gc( true );
        return array_keys( $this->array );
    }


    /**
     * Set garbage collection period.
     *
     * @param int $gc_period amout of interactions with instance of WeakArray
     * before enforcing the garbage collection
     * @throws InvalidArgumentException if garbage collection period is less than 1
     */
    public function setGarbageCollectionPeriod( $gc_period ) {

        $gc_period = intval( $gc_period );
        if( 1 > $gc_period ) {
            throw new InvalidArgumentException( 'Garbage collection period must be greater than 0.' );
        }

        $this->gc_period = $gc_period;
    }


    /* Countable interface */


    public function count() {
        $this->gc( true );
        return count( $this->array );
    }


    /* ArrayAccess interface */


    public function offsetExists( $offset ) {
        $this->gc();
        return isset( $this->array[ $offset ] ) && $this->array[ $offset ]->valid();
    }


    public function offsetGet( $offset ) {
        $this->gc();
        return isset( $this->array[ $offset ] ) ? $this->array[ $offset ]->get() : null;
    }


    public function offsetSet( $offset, $value ) {

        if( !is_object( $value ) ) {
            throw new InvalidArgumentException( sprintf( 'WeakArray can hold only objects, "%s" given.', gettype( $value ) ) );
        }

        $reference = new WeakRef( $value );

        unset( $this[ $offset ] );
        if( null === $offset ) {

            $this->array[] = $reference;

            // Get index of last inserted item
            $array_copy = $this->array;
            end( $array_copy );
            $offset = key( $array_copy );
        } else {
            $this->array[ $offset ] = $reference;
        }

        $dd = isset( $this->destruction_detectors[ $value ] )
                ? $this->destruction_detectors[ $value ]
                : [];

        $dd[ $offset ] = new DestructionDetector( $this, $offset );
        $this->destruction_detectors[ $value ] = $dd;

        $this->gc();
        $this->notify( new Event( $this, Event::OBJECT_SET, $offset ) );

        return $value;
    }


    public function offsetUnset( $offset ) {

        $value = $this[ $offset ];

        if( $value ) {

            $dd = $this->destruction_detectors[ $value ];
            $dd[ $offset ]->deactivate();
            unset( $dd[ $offset ] );
            $this->destruction_detectors[ $value ] = $dd;

            unset( $this->array[ $offset ] );
            $this->notify( new Event( $this, Event::OBJECT_UNSET, $offset ) );
        }

        $this->gc();
    }


    /* Iterator interface */


    public function rewind() {
        $this->gc();
        reset( $this->array );
    }


    public function current() {
        /*
         * https://xkcd.com/292/
         *
         * Yes, it is possible to rewrite this method (and next one)
         * with recursion or with infinite "do { ... } while(true)" loop,
         * but in first case it can easily lead to stack overflow on big arrays,
         * and in second one it just have meaningless evaluation in "while".
         */
        $this->gc();
        loop: {

            $reference = current( $this->array );
            if( false === $reference ) {
                return false;
            }

            $value = $reference->get();
            if( $value ) {
                return $value;
            }

            unset( $this->array[ key( $this->array ) ] );

            goto loop;
        }
    }


    public function key() {
        $this->gc();
        loop: {

            $key = key( $this->array );
            if( null === $key ) {
                return null;
            }

            if( $this->array[ $key ]->valid() ) {
                return $key;
            }

            unset( $this->array[ $key ] );

            goto loop;
        }
    }


    public function next() {
        $this->gc();
        $next = next( $this->array );
        while( false !== $next && !$next->valid() ) {
            unset( $this->array[ key( $this->array ) ] );
            $next = current( $this->array );
        }
    }


    public function valid() {
        $this->gc();
        while( null !== ( $key = key( $this->array ) ) ) {
            if( $this->array[ $key ]->valid() ) {
                return true;
            } else {
                unset( $this->array[ $key ] );
            }
        }
        return $key;
    }


    /* SplSubject interface */


    /**
     * {@inheritDoc}
     * @see SplSubject::attach()
     */
    public function attach( SplObserver $observer ) {
        $this->observers->attach( $observer );
    }


    /**
     * {@inheritDoc}
     * @see SplSubject::detach()
     */
    public function detach( SplObserver $observer ) {
        $this->observers->detach( $observer );
    }


    /**
     * {@inheritDoc}
     * @see SplSubject::notify()
     */
    public function notify() {

        $event = @func_get_arg( 0 );

        if( !$event instanceof Event ) {
            $event = new Event( $this, Event::NOTIFIED, null );
        }

        foreach( $this->observers as $observer ) {
            $observer->update( $event );
        }
    }

}
