<?php

namespace IDCT\HashAlgo;

use IDCT\IHashAlgo;

class Md5 implements IHashAlgo
{
    /**
     * {@inheritdoc}
     */
    public function hash($string)
    {
        return md5($string);
    }
}
