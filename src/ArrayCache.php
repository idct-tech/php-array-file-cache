<?php
namespace IDCT;

abstract class ArrayCache implements \ArrayAccess
{
    /**
     * Path to which save and from which to load files.
     * @var string
     */
    protected $cachePath;

    /**
     * Gets the cache path.
     * @return string
     */
    public function getCachePath()
    {
        return $this->cachePath;
    }

    abstract public function offsetSet($offset, $value);
    abstract public function offsetGet($offset);
    abstract public function offsetExists($offset);
    abstract public function offsetUnset($offset);
}