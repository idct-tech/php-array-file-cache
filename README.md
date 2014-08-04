idct-php-array-file-cache
=========================

Simple file-based cache system for PHP

example
=======

````php
use IDCT\FileArrayCache;

$cache = new FileArrayCache("/tmp");

//saving:
$yourData = array('value_int' => 1, 'value_string' => 'test');
$cache["myCacheKey"] = $yourData;

//reading:
$data = $cache["myCacheKey"];

````