<?php

namespace IDCT\Codec;

use IDCT\ICodec;

class SerializeCodec implements ICodec
{
    /**
     * @inheritdoc
    */
    public function encode($object)
    {
        return serialize($object);
    }

    /**
     * @inheritdoc
    */
    public function decode($string)
    {
        return unserialize($string);
    }
}
