<?php

if( function_exists( 'opcache_reset' ) ) {
    opcache_reset();
}
gc_enable();

require_once __DIR__.'/../vendor/autoload.php';
