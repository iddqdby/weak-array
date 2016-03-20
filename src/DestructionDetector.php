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

use WeakRef;


/**
 * DestructionDetector notifies an instance of WeakArray
 * when it destructs by Garbage Collector.
 */
final class DestructionDetector {

    private $ref_weak_array;
    private $key;
    private $active = true;


    /**
     * Create new destruction detector.
     *
     * @param WeakArray $weak_array an instance of WeakArray
     * @param int|string $key the key
     */
    public function __construct( WeakArray $weak_array, $key ) {
        $this->ref_weak_array = new WeakRef( $weak_array );
        $this->key = $key;
    }


    public function __destruct() {
        if( $this->active && ( $weak_array = $this->ref_weak_array->get() ) ) {
            $weak_array->notify( new Event( $weak_array, Event::OBJECT_DESTRUCTED, $this->key ) );
        }
    }


    /**
     * Deactivate destruction detector.
     */
    public function deactivate() {
        $this->active = false;
    }

}
