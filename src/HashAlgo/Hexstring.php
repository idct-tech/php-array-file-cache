<?php

namespace IDCT\HashAlgo;

use IDCT\IHashAlgo;

class Hexstring implements IHashAlgo
{
    /**
     * {@inheritdoc}
     */
    public function hash($string)
    {
        $result = '';
        if (empty($string)) {
            return $result;
        }

        foreach (str_split($string) as $char) {
            $result .= dechex(ord($char));
        }

        return $result;
    }
}
