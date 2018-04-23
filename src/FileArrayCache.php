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
     * Constructs the new object. Requires a cache path to be given.
     *
     * @param string $cachePath With the trailing slash
     * @throws \Exception Could not initialize the cache directory.
     */
    public function __construct($cachePath)
    {
        $this->cachePath = $cachePath;
        if (!file_exists($cachePath)) {
            if (false === mkdir($cachePath, 0744, true)) {
                throw new \Exception("Could not initialize the cache directory.");
            }
        }
    }

    /**
     * Gets the cache path
     * @return string
     */
    public function getCachePath()
    {
        return $this->cachePath;
    }

    /**
     * Saves the serialized value to the the $cachePath with the given name
     * @param string $offset ID of the cache key (filename)
     * @param string $value Value to be serialized and saved
     */
    public function offsetSet($offset, $value)
    {
        $hash = md5($offset);
        $dir = $this->getParsedKey($hash);
        if (!file_exists($this->getCachePath() . $dir)) {
            mkdir($this->getCachePath() . $dir, 0744, true);
        }
        $filePath = $this->getCachePath() . $dir . $hash;
        if ($this->offsetExists($filePath)) {
            unlink($filePath);
        }
        $serialized = $this->encode($value);
        file_put_contents($filePath, $serialized);
        unset($serialized);

        return $filePath;
    }

    /**
     * Checks if cache key (filename) exists
     * @param string $offset Cache key (Filename)
     * @return boolean
     */
    public function offsetExists($offset)
    {
        $hash = md5($offset);
        $filePath = $this->getCachePath() . $this->getParsedKey($hash) . $hash;

        return file_exists($filePath);
    }

    /**
     * Removes cache key and value (file)
     * @param string $offset Cache key (Filename)
     */
    public function offsetUnset($offset)
    {
        $hash = md5($offset);
        $filePath = $this->getCachePath() . $this->getParsedKey($hash) . $hash;
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
        $filePath = $this->getCachePath() . $this->getParsedKey($hash) . $hash;

        return $this->offsetExists($offset) ? $this->decode(file_get_contents($filePath)) : null;
    }
    /**
     * Encoding method.
     *
     * @var Serializable $data
     * @return string
     */
    protected function encode($data)
    {
        return json_encode($data);
    }

    /**
     * Decoding method.
     *
     * @var string $data
     * @return Serializable
     */
    protected function decode($data)
    {
        return json_decode($data);
    }

    protected function getParsedKey($hash)
    {
        $dir = substr($hash, 0, 2) . "/" . substr($hash, 2, 2) . "/";

        return $dir;
    }
}
