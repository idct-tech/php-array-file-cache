<?php

namespace IDCT;

use IDCT\Codec\SerializeCodec;

/**
 * {@inheritdoc}
 * @deprecated
 */
class SerializedFileArrayCache extends FileArrayCache implements \ArrayAccess
{
    /**
     * {@inheritdoc}
     * @deprecated
     */
    public function __construct($cachePath, $levels = 2, IHashAlgo $hashAlgo = null, ICodec $codec = null)
    {
        trigger_error("Warning: this class is deprecated and will be removed in the future. Remains here only for backwards compatiblity. Please use FileArrayCache with SerializeCodec if you wish to keep the same functionality", E_USER_NOTICE);
        parent::__construct($cachePath, $levels, $hashAlgo, new SerializeCodec);
    }
}
