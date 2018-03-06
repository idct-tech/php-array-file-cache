<?php

namespace IDCT;

class SerializedFileArrayCache extends FileArrayCache implements \ArrayAccess
{
    /**
     * Encoding method.
     *
     * @var Serializable $data
     * @return string
     */
    protected function encode($data)
    {
        return serialize($data);
    }

    /**
     * Decoding method.
     *
     * @var string $data
     * @return Serializable
     */
    protected function decode($data)
    {
        return unserialize($data);
    }
}
