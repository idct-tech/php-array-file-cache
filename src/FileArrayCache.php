<?php
namespace IDCT;

class FileArrayCache implements \ArrayAccess
{
    /**
     * Path to which save and from which to load files
     * @var string
     */
    protected $cachePath;

    /**
     * Gets the cache path
     * @return string
     */
    public function getCachePath()
    {
        return $this->cachePath;
    }

    /**
     * Constructs the new object. Requires a cache path to be given.
     *
     * @param string $cachePath With the trailing slash
     * @throws \Exception Could not initialize the cache directory.
     */
    public function __construct($cachePath)
    {
        $this->cachePath = $cachePath;
        if (!file_exists($cachePath)) {
            if(false === mkdir($cachePath, 0777, true))
            {
                throw new \Exception("Could not initialize the cache directory.");
            }
        }
    }

    /**
     * Saves the serialized value to the the $cachePath with the given name
     * @param string $offset ID of the cache key (filename)
     * @param string $value Value to be serialized and saved
     */
    public function offsetSet($offset, $value)
    {
        $filePath = $this->getCachePath() . $offset;
        if($this->offsetExists($filePath)) {
            unlink($filePath);
        }
        file_put_contents($filePath, serialize($value));
    }

    /**
     * Checks if cache key (filename) exists
     * @param string $offset Cache key (Filename)
     * @return boolean
     */
    public function offsetExists($offset) {
        $filePath = $this->getCachePath() . $offset;
        return file_exists($filePath);
    }

    /**
     * Removes cache key and value (file)
     * @param string $offset Cache key (Filename)
     */
    public function offsetUnset($offset) {
        $filePath = $this->getCachePath() . $offset;
        unlink($filePath);
    }

    /**
     * Gets the value from under the given cache key (filename)
     * @param string $offset Cache key (filename)
     * @return mixed
     */
    public function offsetGet($offset) {
        $filePath = $this->getCachePath() . $offset;
        return $this->offsetExists($offset) ? unserialize(file_get_contents($filePath)) : null;
    }
}