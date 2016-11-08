<?php

namespace IDCT;

use IDCT\ArrayCache;

class FileArrayCache extends ArrayCache
{
    /**
     * Constructs the new object. Requires a cache path to be provided.
     *
     * @param string $cachePath With the trailing slash
     * @throws \Exception Could not initialize the cache directory
     */
    public function __construct($cachePath)
    {
        $this->cachePath = $cachePath;
        if (!file_exists($cachePath)) {
            if(false === mkdir($cachePath, 0644, true)) {
                throw new \Exception("Could not initialize the cache directory.");
            }
        }

    }

    /**
     * Builds the directory path from the given key. Cache if built using a schema
     * in which child folders are always grouped by first two symbols of the md5
     * hash of the key. Such structure is built up to two levels.
     *
     * @param string Cache key
     * @return string
     */
    protected function getParsedPrefix($hash)
    {
        $dir = substr($hash, 0, 2) . "/" . substr($hash, 2, 2) . "/";

        return $dir;
    }

    /**
     * Saves the serialized value to the the $cachePath with the given name.
     * @param string $offset ID of the cache key (filename)
     * @param string $value Value to be serialized and saved
     */
    public function offsetSet($offset, $value)
    {
        $hash = md5($offset);
        $dir = $this->getParsedPrefix($hash);
        if (!file_exists($this->getCachePath() . $dir)) {
            mkdir($this->getCachePath() . $dir, 0644, true);
        }
        $filePath = $this->getCachePath() . $dir . $hash;
        if ($this->offsetExists($filePath)) {
            unlink($filePath);
        }
        file_put_contents($filePath, serialize($value));
        return $filePath;
    }

    /**
     * Checks if cache key (filename) exists.
     * @param string $offset Cache key (Filename)
     * @return boolean
     */
    public function offsetExists($offset)
    {
        $hash = md5($offset);
        $filePath = $this->getCachePath() . $this->getParsedPrefix($hash) . $hash;
        return file_exists($filePath);
    }

    /**
     * Removes cache key and value (file).
     * @param string $offset Cache key (Filename)
     */
    public function offsetUnset($offset)
    {
        $hash = md5($offset);
        $filePath = $this->getCachePath() . $this->getParsedPrefix($hash) . $hash;
        unlink($filePath);
    }

    /**
     * Gets the value from under the given cache key (filename)
     * @param string $offset Cache key (filename)
     * @return mixed
     */
    public function offsetGet($offset)
    {
        $hash = md5($offset);
        $filePath = $this->getCachePath() . $this->getParsedPrefix($hash) . $hash;
        return $this->offsetExists($offset) ? unserialize(file_get_contents($filePath)) : null;
    }

}
