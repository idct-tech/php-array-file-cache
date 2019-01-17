<?php

namespace IDCT;

interface ICodec
{
    /**
     * Encodes an object into a string which allows to store it in a file.
     *
     * @param object $object
     * @return string
     */
    public function encode($object);

    /**
     * Decodes a previosuly encoded using the same codec string back into an object.
     *
     * @param string $string
     * @return object
     */
    public function decode($string);
}
