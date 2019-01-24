<?php

namespace IDCT;

use IDCT\Codec\JsonCodec;
use IDCT\HashAlgo\Md5;

/**
 * Simple file array cache which allows storing and retrieval of objects in and
 * from files using array interface.
 *
 * Usage example:
 *
 * - Storing:
 * ```php
 * $cache['my-key'] = $object;
 * ```
 *
 * - Retrieval:
 * ```php
 * $object = $cache['my-key'];
 * ```
 *
 * During construction you need to provide few crucial arguments:
 * - `$cachePath`
 * A writable and readable path under which cached files will be stored. Must
 * allow creation of directories.
 *
 * - `$levels = 2`
 * Amount of subfolder levels used to limit files count in a single folder.
 * Defaults to 2.
 *
 * - IHashAlgo $hashAlgo
 * Algorithm used to build the filename: a hash out of the given key. Must
 * implement IHashAlgo.
 * Defaults to Md5.
 *
 * - ICodec $codec
 * Encoder / decoder which converts provided objects into strings (and the other
 * way around) stored in files.
 * Defaults to JsonCodec.
 *
 * @todo Error handling
 * @todo Logging
 */
class FileArrayCache implements \ArrayAccess
{
    /**
     * Amount of subfolder levels to generate out of hashes. Hash will be split
     * into that amount of 2-chars chunks. If hash is not long enough then zero
     * symbols shall be sued
     *
     * @var int
     */
    private $levels;

    /**
     * A prepared during initialization string with placeholders and directory
     * separators required to create a path for saving a final cache entry.
     *
     * For example if $levels = 3 and DIRECTORY_SEPARATOR = '/':
     * %s/%s/%s/
     *
     * @var string
     */
    private $levelsString;

    /**
     * Hashing algorithm used for generation of the index hashes (cache keys).
     *
     * @var IHashAlgo
     */
    private $hashAlgo;

    /**
     * Path to which save and from which to load files.
     *
     * @var string
     */
    private $cachePath;

    /**
     * Encoder / Decoded used for converting objects into and from strings.
     *
     * @var ICodec
     */
    private $codec;

    /**
     * Constructs the new object. Requires a cache path to be given.
     *
     * @param string $cachePath With the trailing slash
     * @param int $levels Amount of subfolders' levels
     * @param IHashAlgo Hashing algorithm for keys; defaults to Md5
     * @throws \Exception Could not initialize the cache directory.
     */
    public function __construct($cachePath, $levels = 2, IHashAlgo $hashAlgo = null, ICodec $codec = null)
    {
        if (substr($cachePath, -1, 1) !== DIRECTORY_SEPARATOR) {
            $cachePath .= DIRECTORY_SEPARATOR;
            \trigger_error('Last character of $cachePath\' (' . $cachePath . ') is not directory separator (' . DIRECTORY_SEPARATOR . '); adding it.', E_USER_NOTICE);
        }

        $this->cachePath = $cachePath;
        if (!file_exists($cachePath)) {
            if (false === mkdir($cachePath, 0744, true)) {
                throw new \Exception('Could not initialize the cache directory.');
            }
        }

        $this->hashAlgo = ($hashAlgo === null) ? new Md5 : $hashAlgo;
        if (!is_int($levels) || $levels < 0 || $levels > 10) {
            throw new \Exception('evels must be an integer not greater than 10.');
        }

        $this->codec = ($codec === null) ? new JsonCodec : $codec;
        $this->buildLevelsString($levels);
        $this->levels = $levels;
        $this->verifyOrBuildConfigFile();
    }

    /**
     * Removes all entries in the cache. Keeps `cache_config` and cache's folder.
     *
     * @return $this
     */
    public function clearCache()
    {
        $objects = scandir($this->getCachePath());
        $dir = $this->getCachePath();
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir.$object)) {
                    rrmdir($dir.$object);
                } elseif ($object !== 'cache_config') {
                    unlink($dir.$object);
                }
            }
        }

        return $this;
    }

    /**
     * Returns the cache path.
     *
     * @return string
     */
    public function getCachePath()
    {
        return $this->cachePath;
    }

    /**
     * Saves the serialized value to the the $cachePath under the given key.
     *
     * @param string $offset ID of the cache key (filename)
     * @param string $value Value to be serialized and saved
     */
    public function offsetSet($offset, $value)
    {
        $hash = $this->hashAlgo->hash($offset);
        $dir = $this->buildKeysCachingPath($hash);
        if (!file_exists($this->getCachePath() . $dir)) {
            mkdir($this->getCachePath() . $dir, 0744, true);
        }
        $filePath = $this->getCachePath() . $dir . $hash;
        if ($this->offsetExists($filePath)) {
            unlink($filePath);
        }
        $encoded = $this->codec->encode($value);
        file_put_contents($filePath, $encoded);
        unset($encoded);

        return $this;
    }

    /**
     * Checks if cache key (file) exists.
     *
     * @param string $offset Cache key (Filename)
     * @return boolean
     */
    public function offsetExists($offset)
    {
        $hash = $this->hashAlgo->hash($offset);
        $filePath = $this->getCachePath() . $this->buildKeysCachingPath($hash) . $hash;

        return file_exists($filePath);
    }

    /**
     * Removes cache key and value (file).
     *
     * @param string $offset Cache key (Filename)
     * @return $this
     */
    public function offsetUnset($offset)
    {
        $hash = $this->hashAlgo->hash($offset);
        $filePath = $this->getCachePath() . $this->buildKeysCachingPath($hash) . $hash;
        unlink($filePath);

        return $this;
    }

    /**
     * Gets the value from under the given cache key (filename).
     * Returns null if object not found.
     *
     * @param string $offset Cache key (filename)
     * @return mixed
     */
    public function offsetGet($offset)
    {
        $hash = $this->hashAlgo->hash($offset);
        $filePath = $this->getCachePath() . $this->buildKeysCachingPath($hash) . $hash;

        return $this->offsetExists($offset) ? $this->codec->decode(file_get_contents($filePath)) : null;
    }

    /**
     * Splits string into smaller chunks (defined by $chunkLen). Returns results
     * in an array.
     *
     * @static
     * @param string $string
     * @param int $chunkLen
     * @param string $padding
     * @return string[]
     */
    public static function chunkSplitArr($string, $chunkLen, $padding = '0')
    {
        $count = strlen($string);
        $parts = [];

        for ($i = 0; $i < $count; $i = $i + $chunkLen) {
            if (strlen($string) < $chunkLen) {
                $string .= $padding;
            }
            $parts[] = substr($string, 0, $chunkLen);
            $string = substr($string, $chunkLen);
        }

        return $parts;
    }

    /**
     * Remvoes contents of a directory.
     *
     * @param string $dir
     * @static
     * @return void
     */
    public static function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir."/".$object)) {
                        rrmdir($dir."/".$object);
                    } else {
                        unlink($dir."/".$object);
                    }
                }
            }
            rmdir($dir);
        }
    }

    /**
     * Verifies instance with config file (if exists) or builds a new one if not
     * present.
     *
     * Throws an exception if failed to verify if config.
     *
     * @return $this
     * @throws LogicException
     */
    private function verifyOrBuildConfigFile()
    {
        $configFile = $this->cachePath . DIRECTORY_SEPARATOR . 'cache_config';
        if (!\file_exists($configFile) || !\is_readable($configFile)) {
            //no cache file so we assume a new caching place: creating config file
            $config = [
                'levels' => $this->levels,
                'codec' => get_class($this->codec),
                'hashalgo' => get_class($this->hashAlgo)
            ];

            file_put_contents($configFile, serialize($config));

            return $this;
        }

        $config = unserialize(file_get_contents($configFile));
        if ($config['levels'] !== $this->levels) {
            throw new \LogicException('Levels set (' . $this->levels . ') and one in config (' . $config['levels'] . ') mismatch.');
        }

        if ($config['codec'] !== get_class($this->codec)) {
            throw new \LogicException('Codec set (' . get_class($this->codec) . ') and one in config (' . $config['codec'] . ') mismatch.');
        }

        if ($config['hashalgo'] !== get_class($this->hashAlgo)) {
            throw new \LogicException('Hashing algorithm set (' . get_class($this->hashAlgo) . ') and one in config (' . $config['hashalgo'] . ') mismatch.');
        }

        return $this;
    }

    /**
     * Builds a string with placeholders and directory separators required to
     * create a path for saving a final cache entry.
     *
     * For example if $levels = 3 and DIRECTORY_SEPARATOR = '/':
     * %s/%s/%s/
     *
     * @param int $levels
     * @return $this
     */
    private function buildLevelsString($levels)
    {
        $this->levelsString = join(DIRECTORY_SEPARATOR, array_fill(0, $levels, '%s')) . DIRECTORY_SEPARATOR;

        return $this;
    }

    /**
     * Builds the path of subfolders for the given key using $levels and
     * $levelsString.
     *
     * @param string $hash
     * @return string for example /a0/bd/ce/ if hash starts with A0BDCE and
     * levels count is equal to 3
     */
    private function buildKeysCachingPath($hash)
    {
        $chunks = self::chunkSplitArr($hash, 2, '0');
        $chunksCount = count($chunks);
        if ($this->levels > $chunksCount) {
            $diff = $this->levels - $chunksCount;
            $tempArray = array_fill(0, $diff, '00');
            $chunks = array_merge($chunks, $tempArray);
        }

        return vsprintf($this->levelsString, $chunks);
    }
}
