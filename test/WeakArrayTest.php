<?php

require_once __DIR__.'/WeakArrayTest_Observer.php';

use WeakArray\WeakArray;
use WeakArray\Event;


/**
 * @coversDefaultClass \WeakArray\WeakArray
 */
class WeakArrayTest extends PHPUnit_Framework_TestCase {

    private $objects;
    private $weak_array;


    public function setUp() {

        $foo = new stdClass();
        $bar = new stdClass();
        $baz = new stdClass();
        $one = new stdClass();
        $two = new stdClass();
        $three = new stdClass();

        $foo->val = 'foo';
        $bar->val = 'bar';
        $baz->val = 'baz';
        $one->val = 1;
        $two->val = 2;
        $three->val = 3;

        $this->objects = [
            'foo' => $foo,
            'bar' => $bar,
            'baz' => $baz,
            1 => $one,
            2 => $two,
            3 => $three,
        ];

        $this->weak_array = new WeakArray( $this->objects );
    }


    public function tearDown() {
        $this->objects = null;
        $this->weak_array = null;
    }


    /* Tests */


    /**
     * @covers WeakArray::__construct
     * @expectedException \InvalidArgumentException
     * @expectedExceptiomMessageRegExp /WeakArray can hold only objects, "(boolean|integer|double|string|array|resource|NULL|unknown type)" given\./
     * @dataProvider provider_Exception
     */
    public function test_construct_Exception( array $array ) {
        new WeakArray( $array );
    }


    /**
     * @covers WeakArray::keys
     */
    public function test_keys() {

        $this->assertEquals( array_keys( $this->objects ), $this->weak_array->keys() );

        unset( $this->objects['foo'] );
        unset( $this->objects['bar'] );

        $this->assertEquals( array_keys( $this->objects ), $this->weak_array->keys() );

        unset( $this->objects['baz'] );
        unset( $this->objects[1] );
        unset( $this->objects[2] );
        unset( $this->objects[3] );

        $this->assertEquals( array_keys( $this->objects ), $this->weak_array->keys() );
    }


    /**
     * @covers WeakArray::count
     */
    public function test_count() {

        $this->assertEquals( count( $this->objects ), count( $this->weak_array ) );
        $this->assertEquals( count( $this->objects ), count( $this->weak_array, COUNT_RECURSIVE ) );
        $this->assertEquals( count( $this->objects ), $this->weak_array->count() );

        unset( $this->objects['foo'] );
        unset( $this->objects['bar'] );

        $this->assertEquals( count( $this->objects ), count( $this->weak_array ) );
        $this->assertEquals( count( $this->objects ), count( $this->weak_array, COUNT_RECURSIVE ) );
        $this->assertEquals( count( $this->objects ), $this->weak_array->count() );

        unset( $this->objects['baz'] );
        unset( $this->objects[1] );
        unset( $this->objects[2] );
        unset( $this->objects[3] );

        $this->assertEquals( count( $this->objects ), count( $this->weak_array ) );
        $this->assertEquals( count( $this->objects ), count( $this->weak_array, COUNT_RECURSIVE ) );
        $this->assertEquals( count( $this->objects ), $this->weak_array->count() );
    }


    /**
     * @covers WeakArray::offsetExists
     */
    public function test_offsetExists() {

        $this->assertArrayHasKey( 'foo', $this->weak_array );
        $this->assertArrayHasKey( 'bar', $this->weak_array );
        $this->assertArrayHasKey( 'baz', $this->weak_array );
        $this->assertArrayHasKey( 1, $this->weak_array );
        $this->assertArrayHasKey( 2, $this->weak_array );
        $this->assertArrayHasKey( 3, $this->weak_array );

        $this->assertArrayNotHasKey( 0, $this->weak_array );
        $this->assertArrayNotHasKey( '', $this->weak_array );
        $this->assertArrayNotHasKey( 999, $this->weak_array );
        $this->assertArrayNotHasKey( 'aaa', $this->weak_array );

        unset( $this->objects['foo'] );
        unset( $this->objects['bar'] );

        $this->assertArrayNotHasKey( 'foo', $this->weak_array );
        $this->assertArrayNotHasKey( 'bar', $this->weak_array );

        $this->assertArrayHasKey( 'baz', $this->weak_array );
        $this->assertArrayHasKey( 1, $this->weak_array );
        $this->assertArrayHasKey( 2, $this->weak_array );
        $this->assertArrayHasKey( 3, $this->weak_array );

        $this->assertArrayNotHasKey( 0, $this->weak_array );
        $this->assertArrayNotHasKey( '', $this->weak_array );
        $this->assertArrayNotHasKey( 999, $this->weak_array );
        $this->assertArrayNotHasKey( 'aaa', $this->weak_array );

        unset( $this->objects['baz'] );
        unset( $this->objects[1] );
        unset( $this->objects[2] );
        unset( $this->objects[3] );

        $this->assertArrayNotHasKey( 'foo', $this->weak_array );
        $this->assertArrayNotHasKey( 'bar', $this->weak_array );
        $this->assertArrayNotHasKey( 'baz', $this->weak_array );
        $this->assertArrayNotHasKey( 1, $this->weak_array );
        $this->assertArrayNotHasKey( 2, $this->weak_array );
        $this->assertArrayNotHasKey( 3, $this->weak_array );
        $this->assertArrayNotHasKey( 0, $this->weak_array );
        $this->assertArrayNotHasKey( '', $this->weak_array );
        $this->assertArrayNotHasKey( 999, $this->weak_array );
        $this->assertArrayNotHasKey( 'aaa', $this->weak_array );
    }


    /**
     * @covers WeakArray::offsetGet
     */
    public function test_offsetGet() {

        $this->assertSame( $this->objects['foo'], $this->weak_array['foo'] );
        $this->assertSame( $this->objects['bar'], $this->weak_array['bar'] );
        $this->assertSame( $this->objects['baz'], $this->weak_array['baz'] );
        $this->assertSame( $this->objects[1], $this->weak_array[1] );
        $this->assertSame( $this->objects[2], $this->weak_array[2] );
        $this->assertSame( $this->objects[3], $this->weak_array[3] );

        $this->assertNull( $this->weak_array[0] );
        $this->assertNull( $this->weak_array[''] );
        $this->assertNull( $this->weak_array[123] );
        $this->assertNull( $this->weak_array['ccc'] );

        unset( $this->objects['foo'] );
        unset( $this->objects['bar'] );

        $this->assertNull( $this->weak_array['foo'] );
        $this->assertNull( $this->weak_array['bar'] );

        $this->assertSame( $this->objects['baz'], $this->weak_array['baz'] );
        $this->assertSame( $this->objects[1], $this->weak_array[1] );
        $this->assertSame( $this->objects[2], $this->weak_array[2] );
        $this->assertSame( $this->objects[3], $this->weak_array[3] );

        $this->assertNull( $this->weak_array[0] );
        $this->assertNull( $this->weak_array[''] );
        $this->assertNull( $this->weak_array[123] );
        $this->assertNull( $this->weak_array['ccc'] );

        unset( $this->objects['baz'] );
        unset( $this->objects[1] );
        unset( $this->objects[2] );
        unset( $this->objects[3] );

        $this->assertNull( $this->weak_array['foo'] );
        $this->assertNull( $this->weak_array['bar'] );
        $this->assertNull( $this->weak_array['baz'] );
        $this->assertNull( $this->weak_array[1] );
        $this->assertNull( $this->weak_array[2] );
        $this->assertNull( $this->weak_array[3] );
        $this->assertNull( $this->weak_array[0] );
        $this->assertNull( $this->weak_array[''] );
        $this->assertNull( $this->weak_array[123] );
        $this->assertNull( $this->weak_array['ccc'] );
    }


    /**
     * @covers WeakArray::offsetSet
     * @covers WeakArray::offsetGet
     * @covers WeakArray::count
     * @covers WeakArray::keys
     */
    public function test_offsetSet() {

        $zero = new stdClass();
        $empty_str = new stdClass();
        $ccc = new stdClass();
        $n123 = new stdClass();
        $n999 = new stdClass();

        $zero->val = 0;
        $empty_str->val = '';
        $ccc->val = 'ccc';
        $n123->val = 123;
        $n999->val = 999;


        $this->assertNull( $this->weak_array[0] );
        $this->assertNull( $this->weak_array[''] );
        $this->assertNull( $this->weak_array['ccc'] );
        $this->assertNull( $this->weak_array[123] );
        $this->assertNull( $this->weak_array[999] );
        $this->assertEquals(
                count( $this->objects ),
                count( $this->weak_array )
        );
        $this->assertEquals(
                array_keys( $this->objects ),
                $this->weak_array->keys()
        );


        $this->weak_array[0] = $zero;


        $this->assertSame( $zero, $this->weak_array[0] );

        $this->assertNull( $this->weak_array[''] );
        $this->assertNull( $this->weak_array['ccc'] );
        $this->assertNull( $this->weak_array[123] );
        $this->assertNull( $this->weak_array[999] );
        $this->assertEquals(
                count( $this->objects ) + 1,
                count( $this->weak_array )
        );
        $this->assertEquals(
                array_merge( array_keys( $this->objects ), [ 0 ] ),
                $this->weak_array->keys()
        );


        $this->weak_array[''] = $empty_str;
        $this->weak_array['ccc'] = $ccc;
        $this->weak_array[123] = $n123;
        $this->weak_array[999] = $n999;


        $this->assertSame( $zero, $this->weak_array[0] );
        $this->assertSame( $empty_str, $this->weak_array[''] );
        $this->assertSame( $ccc, $this->weak_array['ccc'] );
        $this->assertSame( $n123, $this->weak_array[123] );
        $this->assertSame( $n999, $this->weak_array[999] );
        $this->assertEquals(
                count( $this->objects ) + 5,
                count( $this->weak_array )
        );
        $this->assertEquals(
                array_merge( array_keys( $this->objects ), [ 0, '', 'ccc', 123, 999 ] ),
                $this->weak_array->keys()
        );


        unset( $zero );
        unset( $empty_str );
        unset( $ccc );
        unset( $n123 );
        unset( $n999 );


        $this->assertNull( $this->weak_array[0] );
        $this->assertNull( $this->weak_array[''] );
        $this->assertNull( $this->weak_array['ccc'] );
        $this->assertNull( $this->weak_array[123] );
        $this->assertNull( $this->weak_array[999] );
        $this->assertEquals(
                count( $this->objects ),
                count( $this->weak_array )
        );
        $this->assertEquals(
                array_keys( $this->objects ),
                $this->weak_array->keys()
        );


        $obj = new stdClass();
        $this->weak_array[] = $obj;


        $this->assertSame( $obj, $this->weak_array[1000] );
        $this->assertEquals(
                count( $this->objects ) + 1,
                count( $this->weak_array )
        );
        $this->assertEquals(
                array_merge( array_keys( $this->objects ), [ 1000 ] ),
                $this->weak_array->keys()
        );


        unset( $obj );


        $this->assertNull( $this->weak_array[1000] );
        $this->assertEquals(
                count( $this->objects ),
                count( $this->weak_array )
        );
        $this->assertEquals(
                array_keys( $this->objects ),
                $this->weak_array->keys()
        );
    }


    /**
     * @covers WeakArray::offsetSet
     * @expectedException \InvalidArgumentException
     * @expectedExceptiomMessageRegExp /WeakArray can hold only objects, (boolean|integer|double|string|array|resource|NULL|unknown type) given\./
     * @dataProvider provider_Exception
     */
    public function test_offsetSet_Exception( array $array ) {
        foreach( $array as $obj ) {
            $this->weak_array[] = $obj;
        }
    }


    /**
     * @covers WeakArray::offsetUnset
     */
    public function test_offsetUnset() {

        unset( $this->weak_array[0] );
        unset( $this->weak_array[''] );
        unset( $this->weak_array['ccc'] );
        unset( $this->weak_array[123] );
        unset( $this->weak_array[999] );


        $this->assertSame( $this->objects['foo'], $this->weak_array['foo'] );
        $this->assertSame( $this->objects['bar'], $this->weak_array['bar'] );
        $this->assertSame( $this->objects['baz'], $this->weak_array['baz'] );
        $this->assertSame( $this->objects[1], $this->weak_array[1] );
        $this->assertSame( $this->objects[2], $this->weak_array[2] );
        $this->assertSame( $this->objects[3], $this->weak_array[3] );

        $this->assertEquals(
                count( $this->objects ),
                count( $this->weak_array )
        );
        $this->assertEquals(
                array_keys( $this->objects ),
                $this->weak_array->keys()
        );


        unset( $this->weak_array['foo'] );
        unset( $this->weak_array['bar'] );


        $this->assertNull( $this->weak_array['foo'] );
        $this->assertNull( $this->weak_array['bar'] );

        $this->assertSame( $this->objects['baz'], $this->weak_array['baz'] );
        $this->assertSame( $this->objects[1], $this->weak_array[1] );
        $this->assertSame( $this->objects[2], $this->weak_array[2] );
        $this->assertSame( $this->objects[3], $this->weak_array[3] );

        $this->assertEquals(
                count( $this->objects ) - 2,
                count( $this->weak_array )
        );

        $keys_expected = array_diff( array_keys( $this->objects ), [ 'foo', 'bar' ] );
        $keys_actual = $this->weak_array->keys();
        sort( $keys_expected );
        sort( $keys_actual );

        $this->assertEquals( $keys_expected, $keys_actual );


        unset( $this->weak_array['baz'] );
        unset( $this->weak_array[1] );
        unset( $this->weak_array[2] );
        unset( $this->weak_array[3] );


        $this->assertNull( $this->weak_array['foo'] );
        $this->assertNull( $this->weak_array['bar'] );
        $this->assertNull( $this->weak_array['baz'] );
        $this->assertNull( $this->weak_array[1] );
        $this->assertNull( $this->weak_array[2] );
        $this->assertNull( $this->weak_array[3] );

        $this->assertEquals(
                count( $this->objects ) - 6,
                count( $this->weak_array )
        );

        $keys_expected = array_diff( array_keys( $this->objects ), [ 'foo', 'bar', 'baz', 1, 2, 3 ] );
        $keys_actual = $this->weak_array->keys();
        sort( $keys_expected );
        sort( $keys_actual );

        $this->assertEquals( $keys_expected, $keys_actual );
    }


    /**
     * @covers WeakArray::rewind
     * @covers WeakArray::current
     * @covers WeakArray::key
     * @covers WeakArray::next
     * @covers WeakArray::valid
     */
    public function test_foreach() {

        $str_expected = $this->foreach_concatObjectPropertyValues( $this->objects );
        $str_actual = $this->foreach_concatObjectPropertyValues( $this->weak_array );

        $this->assertEquals( $str_expected, $str_actual );


        unset( $this->objects['bar'] );


        $str_expected = $this->foreach_concatObjectPropertyValues( $this->objects );
        $str_actual = $this->foreach_concatObjectPropertyValues( $this->weak_array );

        $this->assertEquals( $str_expected, $str_actual );


        unset( $this->weak_array['foo'] );


        $str_expected = $this->foreach_concatObjectPropertyValues( $this->objects );
        $str_actual = $this->foreach_concatObjectPropertyValues( $this->weak_array );

        $this->assertNotEquals( $str_expected, $str_actual );

        $objects_copy = $this->objects;
        unset( $objects_copy['foo'] );

        $str_expected = $this->foreach_concatObjectPropertyValues( $objects_copy );
        $str_actual = $this->foreach_concatObjectPropertyValues( $this->weak_array );

        $this->assertEquals( $str_expected, $str_actual );


        unset( $objects_copy['baz'] );

        foreach( $this->weak_array as $key => $obj ) {
            if( 1 === $key ) {
                unset( $this->weak_array['baz'] );
            }
        }


        $str_expected = $this->foreach_concatObjectPropertyValues( $objects_copy );
        $str_actual = $this->foreach_concatObjectPropertyValues( $this->weak_array );

        $this->assertEquals( $str_expected, $str_actual );


        unset( $objects_copy[2] );

        foreach( $this->weak_array as $key => $obj ) {
            if( 1 === $key ) {
                unset( $this->objects[2] );
            }
        }


        $str_expected = $this->foreach_concatObjectPropertyValues( $objects_copy );
        $str_actual = $this->foreach_concatObjectPropertyValues( $this->weak_array );

        $this->assertEquals( $str_expected, $str_actual );


        unset( $objects_copy[3] );

        foreach( $this->weak_array as $key => $obj ) {
            if( 1 === $key ) {
                unset( $this->weak_array[3] );
            }
        }


        $str_expected = $this->foreach_concatObjectPropertyValues( $objects_copy );
        $str_actual = $this->foreach_concatObjectPropertyValues( $this->weak_array );

        $this->assertEquals( $str_expected, $str_actual );


        unset( $objects_copy[1] );

        foreach( $this->weak_array as $key => $obj ) {
            if( 1 === $key ) {
                unset( $this->weak_array[ $key ] );
            }
        }


        $str_expected = $this->foreach_concatObjectPropertyValues( $objects_copy );
        $str_actual = $this->foreach_concatObjectPropertyValues( $this->weak_array );

        $this->assertEquals( $str_expected, $str_actual );


        $obj0 = new stdClass();
        $obj1 = new stdClass();
        $obj2 = new stdClass();
        $obj3 = new stdClass();
        $obj4 = new stdClass();

        $obj0->val = 0;
        $obj1->val = 1;
        $obj2->val = 2;
        $obj3->val = 3;
        $obj4->val = 4;

        $objects = [ $obj0, $obj1, $obj2, $obj3, $obj4 ];

        $this->weak_array[0] = $obj0;
        $this->weak_array[1] = $obj1;
        $this->weak_array[2] = $obj2;
        $this->weak_array[3] = $obj3;
        $this->weak_array[4] = $obj4;


        foreach( $this->weak_array as $key => $obj ) {
            if( 3 === $key ) {
                unset( $obj4 );
                unset( $objects[4] );
            }
        }


        $str_expected = $this->foreach_concatObjectPropertyValues( $objects );
        $str_actual = $this->foreach_concatObjectPropertyValues( $this->weak_array );

        $this->assertEquals( $str_expected, $str_actual );


        foreach( $this->weak_array as $key => $obj ) {
            if( 2 === $key ) {
                unset( $this->weak_array[3] );
                unset( $objects[3] );
            }
        }


        $str_expected = $this->foreach_concatObjectPropertyValues( $objects );
        $str_actual = $this->foreach_concatObjectPropertyValues( $this->weak_array );

        $this->assertEquals( $str_expected, $str_actual );
    }


    private function foreach_concatObjectPropertyValues( $objects ) {
        $str = '';
        foreach( $objects as $key => $obj ) {
            $str .= $key.$obj->val;
        }
        return $str;
    }


    /**
     * @covers WeakArray::attach
     * @covers WeakArray::detach
     * @covers WeakArray::notify
     */
    public function test_attach_detach_notify() {

        $obs1 = new WeakArrayTest_Observer();
        $obs2 = new WeakArrayTest_Observer();
        $obs3 = new WeakArrayTest_Observer();

        $obj0 = new stdClass();
        $obj1 = new stdClass();
        $obj2 = new stdClass();
        $obj3 = new stdClass();
        $obj4 = new stdClass();
        $obj5 = new stdClass();
        $obj6 = new stdClass();
        $obj7 = new stdClass();
        $obj8 = new stdClass();
        $obj9 = new stdClass();

        $obj0->val = 0;
        $obj1->val = 1;
        $obj2->val = 2;
        $obj3->val = 3;
        $obj4->val = 4;
        $obj5->val = 5;
        $obj6->val = 6;
        $obj7->val = 7;
        $obj8->val = 8;
        $obj9->val = 9;

        $expected_1 = [];
        $expected_2 = [];
        $expected_3 = [];

        /* -------- */

        $this->weak_array[] = $obj0;

        $this->weak_array->attach( $obs1 );
        $this->weak_array->attach( $obs2 );
        $this->weak_array->attach( $obs3 );

        $this->weak_array[1001] = $obj1;

        $expected_1[] = [ 'subject' => $this->weak_array, 'type' => Event::TYPE_SET, 'key' => 1001 ];
        $expected_2[] = [ 'subject' => $this->weak_array, 'type' => Event::TYPE_SET, 'key' => 1001 ];
        $expected_3[] = [ 'subject' => $this->weak_array, 'type' => Event::TYPE_SET, 'key' => 1001 ];


        $this->assertEquals( $expected_1, $obs1->collected() );
        $this->assertEquals( $expected_2, $obs2->collected() );
        $this->assertEquals( $expected_3, $obs3->collected() );


        $this->weak_array->detach( $obs2 );

        $this->weak_array[] = $obj2;

        $expected_1[] = [ 'subject' => $this->weak_array, 'type' => Event::TYPE_SET, 'key' => 1002 ];
        $expected_3[] = [ 'subject' => $this->weak_array, 'type' => Event::TYPE_SET, 'key' => 1002 ];


        $this->assertEquals( $expected_1, $obs1->collected() );
        $this->assertEquals( $expected_2, $obs2->collected() );
        $this->assertEquals( $expected_3, $obs3->collected() );


        $this->weak_array->attach( $obs3 );

        $this->weak_array[0] = $obj3;

        $expected_1[] = [ 'subject' => $this->weak_array, 'type' => Event::TYPE_SET, 'key' => 0 ];
        $expected_3[] = [ 'subject' => $this->weak_array, 'type' => Event::TYPE_SET, 'key' => 0 ];


        $this->assertEquals( $expected_1, $obs1->collected() );
        $this->assertEquals( $expected_2, $obs2->collected() );
        $this->assertEquals( $expected_3, $obs3->collected() );


        $this->weak_array->detach( $obs2 );
        $this->weak_array->detach( $obs1 );

        $this->weak_array[''] = $obj4;

        $expected_3[] = [ 'subject' => $this->weak_array, 'type' => Event::TYPE_SET, 'key' => '' ];


        $this->assertEquals( $expected_1, $obs1->collected() );
        $this->assertEquals( $expected_2, $obs2->collected() );
        $this->assertEquals( $expected_3, $obs3->collected() );


        $this->weak_array->detach( $obs3 );

        $this->weak_array[999] = $obj5;


        $this->assertEquals( $expected_1, $obs1->collected() );
        $this->assertEquals( $expected_2, $obs2->collected() );
        $this->assertEquals( $expected_3, $obs3->collected() );


        $this->weak_array->attach( $obs1 );
        $this->weak_array->attach( $obs1 );
        $this->weak_array->attach( $obs2 );
        $this->weak_array->attach( $obs2 );
        $this->weak_array->attach( $obs3 );
        $this->weak_array->attach( $obs3 );


        unset( $this->weak_array[1001] );

        $expected_1[] = [ 'subject' => $this->weak_array, 'type' => Event::TYPE_UNSET, 'key' => 1001 ];
        $expected_2[] = [ 'subject' => $this->weak_array, 'type' => Event::TYPE_UNSET, 'key' => 1001 ];
        $expected_3[] = [ 'subject' => $this->weak_array, 'type' => Event::TYPE_UNSET, 'key' => 1001 ];


        $this->assertEquals( $expected_1, $obs1->collected() );
        $this->assertEquals( $expected_2, $obs2->collected() );
        $this->assertEquals( $expected_3, $obs3->collected() );


        unset( $this->weak_array[0] );
        unset( $this->weak_array[''] );

        $expected_1[] = [ 'subject' => $this->weak_array, 'type' => Event::TYPE_UNSET, 'key' => 0 ];
        $expected_2[] = [ 'subject' => $this->weak_array, 'type' => Event::TYPE_UNSET, 'key' => 0 ];
        $expected_3[] = [ 'subject' => $this->weak_array, 'type' => Event::TYPE_UNSET, 'key' => 0 ];

        $expected_1[] = [ 'subject' => $this->weak_array, 'type' => Event::TYPE_UNSET, 'key' => '' ];
        $expected_2[] = [ 'subject' => $this->weak_array, 'type' => Event::TYPE_UNSET, 'key' => '' ];
        $expected_3[] = [ 'subject' => $this->weak_array, 'type' => Event::TYPE_UNSET, 'key' => '' ];


        $this->assertEquals( $expected_1, $obs1->collected() );
        $this->assertEquals( $expected_2, $obs2->collected() );
        $this->assertEquals( $expected_3, $obs3->collected() );


        $this->weak_array->notify();

        $expected_1[] = [ 'subject' => $this->weak_array, 'type' => Event::TYPE_NOTIFY, 'key' => null ];
        $expected_2[] = [ 'subject' => $this->weak_array, 'type' => Event::TYPE_NOTIFY, 'key' => null ];
        $expected_3[] = [ 'subject' => $this->weak_array, 'type' => Event::TYPE_NOTIFY, 'key' => null ];


        $this->assertEquals( $expected_1, $obs1->collected() );
        $this->assertEquals( $expected_2, $obs2->collected() );
        $this->assertEquals( $expected_3, $obs3->collected() );
    }


    /**
     * @covers \WeakArray\Event::__construct
     * @dataProvider provider_Event_construct_Exception
     */
    public function test_Event_construct_Exception( $type, $key, $expected_exception_class, $expected_exception_message ) {

        $this->expectException( $expected_exception_class );
        $this->expectExceptionMessage( $expected_exception_message );

        new Event( $this->weak_array, $type, $key );
    }


    /* Data providers */


    public function provider_Exception() {
        return [
            'boolean'   => [ [ true ] ],
            'integer'   => [ [ new stdClass(), 0 ] ],
            'double'    => [ [ 3.14, new stdClass() ] ],
            'string'    => [ [ new stdClass(), '', new stdClass() ] ],
            'array'     => [ [ [] ] ],
            'resource'  => [ [ fopen( __FILE__, 'r' ) ] ],
            'NULL'      => [ [ null ] ],
        ];
    }


    public function provider_Event_construct_Exception() {
        return [
            'key_boolean' => [
                    Event::TYPE_SET,
                    true,
                    InvalidArgumentException::class,
                    'Key must be NULL, or of type "int" or "string", "boolean" given.',
            ],
            'key_double' => [
                    Event::TYPE_SET,
                    3.14,
                    InvalidArgumentException::class,
                    'Key must be NULL, or of type "int" or "string", "double" given.',
            ],
            'key_array_0' => [
                    Event::TYPE_SET,
                    [],
                    InvalidArgumentException::class,
                    'Key must be NULL, or of type "int" or "string", "array" given.',
            ],
            'key_array_1' => [
                    Event::TYPE_SET,
                    [ 1, 2, 3 ],
                    InvalidArgumentException::class,
                    'Key must be NULL, or of type "int" or "string", "array" given.',
            ],
            'key_resource' => [
                    Event::TYPE_SET,
                    fopen( __FILE__, 'r' ),
                    InvalidArgumentException::class,
                    'Key must be NULL, or of type "int" or "string", "resource" given.',
            ],
            'type_0' => [
                    '',
                    'abc',
                    InvalidArgumentException::class,
                    'Type must be one Event::TYPE_* constants.',
            ],
            'type_1' => [
                    -1,
                    'abc',
                    InvalidArgumentException::class,
                    'Type must be one Event::TYPE_* constants.',
            ],
            'type_2' => [
                    999,
                    'abc',
                    InvalidArgumentException::class,
                    'Type must be one Event::TYPE_* constants.',
            ],
        ];
    }

}
