<?php

namespace IDCT\Codec;

use IDCT\ICodec;

class JsonCodec implements ICodec
{
    /**
     * @inheritdoc
    */
    public function encode($object)
    {
        return json_encode($object);
    }

    /**
     * @inheritdoc
    */
    public function decode($string)
    {
        return json_decode($string);
    }
}
