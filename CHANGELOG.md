CHANGELOG
=========

# 0.2

* Deprecated `SerializedFileArrayCache`
* Created `ICodec` (and codecs `JsonCodec`, `SerializeCodec`).
* Created `IHashAlgo` (and `Md5`, `Hexstrings` implementations).
* Modified `FileArrayCache` to support setting of codec, hashing algos and level
of subfolders in caching path.
* Using lowercase paths: for case-insensitive filesystems.
* First tagged release: SemVer adoption.

# 0.1 (not tagged)

* Initial deployment of the idea: `FileArrayCache`, `SerializedArrayCache`.
