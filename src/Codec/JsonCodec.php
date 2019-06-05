<?php

namespace IDCT\Codec;

use IDCT\ICodec;

class JsonCodec implements ICodec
{
    protected $decodeAsArray = false;

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
        return json_decode($string, $this->decodeAsArray);
    }

    public function decodeAsArray() {
        $this->decodeAsArray = true;
        return $this;
    }

    public function decodeAsClass() {
        $this->decodeAsArray = false;
        return $this;
    }
}
