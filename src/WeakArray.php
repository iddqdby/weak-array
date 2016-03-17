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
use InvalidArgumentException;


/**
 * Array of weak references.
 */
class WeakArray implements Countable, ArrayAccess, Iterator, SplSubject {

    /** @var WeakRef[] */
    protected $array = [];

    /** @var Event */
    protected $last_event;

    /** @var SplObjectStorage<SplObserver, null> */
    protected $observers;


    /**
     * Create new array of weak references.
     *
     * @param array $objects an optional array of objects
     * @throws InvalidArgumentException if array contains value that is not an object
     */
    public function __construct( array $objects = [] ) {
        $this->observers = new SplObjectStorage();
        $this->updateLastEvent();
        foreach( $objects as $key => $obj ) {
            $this[ $key ] = $obj;
        }
    }


    protected function gc( $force = false ) {
        static $i = 0;
        if( $force || ++$i == 1024 ) {
            $i = 0;
            // "array_filter()" resets internal array pointer, so we don't us it;
            // internal array pointer is used in implemented methods of the "Iterator" interface
            foreach( array_keys( $this->array ) as $key ) {
                if( !$this->array[ $key ]->valid() ) {
                    unset( $this->array[ $key ] );
                }
            }
        }
    }


    protected function updateLastEvent( $key = null, $type = Event::TYPE_NOTIFY ) {
        $this->last_event = new Event( $this, $type, $key );
        return $this->last_event;
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

        if( null === $offset ) {
            $this->array[] = $reference;
            // Get index of last inserted item
            $array_copy = $this->array;
            end( $array_copy );
            $offset = key( $array_copy );
        } else {
            $this->array[ $offset ] = $reference;
        }

        $this->gc();
        $this->updateLastEvent( $offset, Event::TYPE_SET );
        $this->notify();

        return $value;
    }


    public function offsetUnset( $offset ) {

        unset( $this->array[ $offset ] );

        $this->gc();
        $this->updateLastEvent( $offset, Event::TYPE_UNSET );
        $this->notify();
    }


    /* Iterator interface */


    public function rewind() {
        reset( $this->array );
    }


    public function current() {
        /*
         * https://xkcd.com/292/
         *
         * Yes, it is possible to rewrite this method (and next one)
         * with recursion or with infinite "do { ... } while(true)" loop,
         * but in first case it can easily lead to stack owerflow on big arrays,
         * and in second one it just have meaningless evaluation in "while".
         */
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
        $next = next( $this->array );
        while( false !== $next && !$next->valid() ) {
            unset( $this->array[ key( $this->array ) ] );
            $next = current( $this->array );
        }
    }


    public function valid() {
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

        foreach( $this->observers as $observer ) {
            $observer->update( $this->last_event );
        }

        $this->updateLastEvent();
    }

}
