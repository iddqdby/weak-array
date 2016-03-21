<?php

if( function_exists( 'opcache_reset' ) ) {
    opcache_reset();
}
gc_enable();

require_once dirname( __DIR__ ).'/autoload.php';
