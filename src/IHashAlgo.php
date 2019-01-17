<?php

namespace IDCT;

interface IHashAlgo
{
    /**
     * Returns a hash built out of provided string.
     *
     * @param string $string
     * @return string
     */
    public function hash($string);
}
