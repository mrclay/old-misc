<?php

namespace MrClay\Crypt;

use MrClay\Crypt\Encoding\EncodingInterface;
use MrClay\Crypt\Encoding\Base64Url;

class Container extends \SplDoublyLinkedList {

    /**
     * Encode to a string
     *
     * @param Encoding\EncodingInterface $encoding
     * @return string
     */
    public function encode(EncodingInterface $encoding = null)
    {
        if (! $encoding) {
            $encoding = new Base64Url();
        }
        $strs = array();
        foreach ($this as $bs) {
            $strs[] = $encoding->encode($bs);
        }
        return implode($encoding->getSeparator(), $strs);
    }

    /**
     * Build a container from an encoded container string
     *
     * @param Encoding\EncodingInterface $encoding
     * @param string $encodedContainer
     * @return false|Container
     */
    public static function decode(EncodingInterface $encoding, $encodedContainer)
    {
        $cont = new Container();
        foreach (explode($encoding->getSeparator(), (string) $encodedContainer) as $encoded)
        {
            $decoded = $encoding->decode($encoded);
            if ($decoded) {
                $cont[] = $decoded;
            } else {
                return false;
            }
        }
        return $cont;
    }

    /**
     * @return array
     */
    public function getSizes()
    {
        $sizes = array();
        foreach ($this as $bs) {
            $sizes[] = $bs->getSize();
        }
        return $sizes;
    }

    /**
     * Get binary string containing all bytes in container
     *
     * @return string
     */
    public function toBytes()
    {
        $bytes = '';
        foreach ($this as $bs) {
            $bytes .= $bs->getBytes();
        }
        return $bytes;
    }

    /**
     * Build a container from a string of bytes
     *
     * @param string $bytes
     * @param array $sizes
     * @return false|Container
     */
    public static function fromBytes($bytes, array $sizes)
    {
        if (strlen($bytes) !== array_sum($sizes)) {
            return false;
        }
        $offset = 0;
        $cont = new self();
        foreach ($sizes as $size) {
            $cont[] = new ByteString(substr($bytes, $offset, $size));
            $offset += $size;
        }
        return $cont;
    }

    /**
     * @param array $byteStrings array of ByteStrings
     */
    public function __construct(array $byteStrings = array())
    {
        foreach ($byteStrings as $bs) {
            $this->push($bs);
        }
    }

    public function push($value)
    {
        if (! $value instanceof ByteString) {
            throw new InvalidArgumentException('Container accepts only ByteStrings');
        }
        parent::push($value);
    }

    public function unshift($value)
    {
        if (! $value instanceof ByteString) {
            throw new InvalidArgumentException('Container accepts only ByteStrings');
        }
        parent::unshift($value);
    }

    public function offsetSet($index, $value)
    {
        if (! $value instanceof ByteString) {
            throw new InvalidArgumentException('Container accepts only ByteStrings');
        }
        parent::offsetSet($index, $value);
    }
}
