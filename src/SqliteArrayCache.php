<?php

namespace IDCT;

use IDCT\ArrayCache;

class SqliteArrayCache extends ArrayCache
{
    /**
     * Database handler instance.
     * @var \SQLite3
     */
    protected $db;

    /**
     * Constructs the new object. Requires cache path to be provider.
     *
     * @param string $cachePath With the trailing slash
     * @throws \Exception Could not initialize the cache directory.
     */
    public function __construct($cachePath, $reset)
    {
        $this->cachePath = $cachePath;
        if (!file_exists($cachePath)) {
            if(false === mkdir($cachePath, 0644, true)) {
                throw new \Exception("Could not initialize the cache directory.");
            }
        }

        $cacheFile = $cachePath . 'cache.db';
        if(file_exists($cacheFile) && $reset === true) {
            unlink($cacheFile);
        }

        $this->db = new \SQLite3($cacheFile);
        $this->db->exec("PRAGMA synchronous = OFF");
        $this->db->exec("PRAGMA journal_mode = MEMORY");
        $this->db->exec("PRAGMA page_size = 31457280");
        $this->db->exec("PRAGMA temp_store = MEMORY");
        $this->db->exec("PRAGMA count_changes = OFF");
        $this->db->exec('BEGIN;
CREATE TABLE cache (
    key VARCHAR(48) PRIMARY KEY,
    value BLOB
    );
CREATE INDEX key_idx ON cache (key);
COMMIT;');
    }

    /**
     * Sqlite treats every single operation as atomic transaction and therefore
     * if not explicitly stated when to do so performs internal BEGIN TRANSACTION
     * and END TRANSACTION / COMMIT for every action. Usually it will be more
     * effective to control the beginning and finishing of a transaction manually
     * during the import / filling stage of a cache / database.
     *
     * This method starts a transaction (opens a transaction).
     *
     * @return $this
     */
    public function startImport()
    {
        $this->db->exec("BEGIN TRANSACTION;");
        return $this;
    }

    /**
     * Sqlite treats every single operation as atomic transaction and therefore
     * if not explicitly stated when to do so performs internal BEGIN TRANSACTION
     * and END TRANSACTION / COMMIT for every action. Usually it will be more
     * effective to control the beginning and finishing of a transaction manually
     * during the import / filling stage of a cache / database.
     *
     * This method ends a transaction (commits a transaction).
     *
     * @return $this
     */
    public function endImport()
    {
        $this->db->exec("END TRANSACTION;");
        return $this;
    }

    /**
     * Saves the serialized value to the the $cachePath with the given name.
     *
     * @param string $offset ID of the cache key
     * @param string $value Value to be serialized and saved
     */
    public function offsetSet($offset, $value)
    {
        $stmt = $this->db->prepare('INSERT OR REPLACE INTO cache (key, value) VALUES (:key, :value)');
        $stmt->bindValue(':key', $offset);
        $stmt->bindValue(':value', serialize($value), SQLITE3_BLOB);
        $stmt->execute();
    }

    /**
     * Checks if cache key exists.
     *
     * @param string $offset Cache key
     * @return boolean
     */
    public function offsetExists($offset)
    {
        $stmt = $this->db->prepare('SELECT value FROM cache WHERE key = :key');
        $stmt->bindValue(':key', $offset);
        $result = $stmt->execute();
        $value = $result->fetchArray(SQLITE3_ASSOC);

        if($value === false || $value === null || empty($value) || !isset($value['value'])) {
            return false;
        }

        return true;
    }

    /**
     * Removes cache key and value.
     *
     * @param string $offset Cache key
     */
    public function offsetUnset($offset)
    {
        $stmt = $this->db->prepare('DELETE FROM cache WHERE key = :key');
        $stmt->bindValue(':key', $offset);
        $result = $stmt->execute();
    }

    /**
     * Gets the value from under the given cache key. Returns null if not found.
     * @param string $offset Cache key.
     *
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        $stmt = $this->db->prepare('SELECT value FROM cache WHERE key = :key');
        $stmt->bindValue(':key', $offset);
        $result = $stmt->execute();
        $value = $result->fetchArray(SQLITE3_ASSOC);

        if($value === false || $value === null || empty($value) || !isset($value['value'])) {
            return null;
        }

        return unserialize($value['value']);
    }

    /**
     * Returns the value from under the given cache key and removes it from cache.
     * Returns null if cache key not found.
     *
     * @param string $offset Cache key.
     * @return mixed|null
     */
    public function pop($offset)
    {
        $value = $this->offsetGet($offset);
        $this->offsetUnset($offset);
        return $value;
    }

}
