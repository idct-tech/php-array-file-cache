idct-php-array-file-cache
=========================

Simple file-based caching system for PHP which allows storing and retrieval of
data in and from files using a simple array interface.

usage example
=============

````php
use IDCT\FileArrayCache;

$cache = new FileArrayCache("/tmp");

//saving:
$yourData = ['value_int' => 1, 'value_string' => 'test'];
$cache["myCacheKey"] = $yourData;

//reading:
$data = $cache["myCacheKey"];

````

To clear all entries in cache, but keep the folder and config use `clearCache()`
method.

installation
============

In case you do not use any package manager in your code then be sure to include
all classes and interfaces.

Suggested way is to use Composer[https://getcomposer.org/] and install the library
in your project using:
```bash
composer require idct/php-array-file-cache
```

initialization
==============

Create an instance of `FileArrayCache` by calling its constructor.
One parameter is mandatory:

* `$cachePath` which must point to a writable and readable directory where cache
will be or is stored. Warning: if a cache with `cache_config` file (after version
0.2) exists already in the folder then initialization will be verified with it:
failure will throw a `LogicException`.

Optional parameters:

* `int $levels`: Amount of subfolder levels used to limit files count in a single
folder. Defaults to 2.

* `IHashAlgo $hashAlgo`: Algorithm used to build the filename: a hash out of the
given key. Must implement `IHashAlgo`. Defaults to `Md5`.

* `ICodec $codec`: Encoder / decoder which converts provided objects into strings
(and the other way around) stored in files. Defaults to `JsonCodec`.

contribution
============

To contribute please just file an issue or merge request (pull request). Whenever
possible please try to keep backwards-compatiblity.

Be sure to follow latest coding standards!
