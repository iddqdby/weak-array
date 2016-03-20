<?php

require_once __DIR__.'/../vendor/autoload.php';

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

?>

EXPECTED OUTPUT:

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
