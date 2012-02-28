<?php

namespace MrClay\Crypt;

use MrClay\Crypt\Encoding\EncodingInterface;
use MrClay\Crypt\Encoding\Base64Url;

/**
 * A container for a list of binary strings. Can be easily encoded to an ASCII environment, or to binary
 *
 * @author Steve Clay <steve@mrclay.org>
 * @license http://www.opensource.org/licenses/mit-license.php  MIT License
 */
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
     * Get the sizes in bytes of each contained byte string
     *
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
     * Get binary string representation of the contents
     *
     * @return string
     */
    public function toBinary()
    {
        $bin = implode(',', $this->getSizes()) . '|';
        foreach ($this as $bs) {
            $bin .= $bs->getBytes();
        }
        return $bin;
    }

    /**
     * Create a container from a binary representation
     *
     * @param $binary
     * @return false|Container
     */
    public static function fromBinary($binary)
    {
        $pieces = explode('|', $binary, 2);
        if (count($pieces) !== 2) {
            return false;
        }
        $sizes = explode(',', $pieces[0]);
        if (strlen($pieces[1]) !== array_sum($sizes)) {
            return false;
        }
        $offset = 0;
        $cont = new self();
        foreach ($sizes as $size) {
            $cont[] = new ByteString(substr($pieces[1], $offset, $size));
            $offset += $size;
        }
        return $cont;
    }

    /**
     * @param array|ByteString $byteStrings array of ByteStrings
     */
    public function __construct($byteStrings = array())
    {
        foreach ((array) $byteStrings as $bs) {
            $this->push($bs);
        }
    }

    public function push($value)
    {
        if (! $value instanceof ByteString) {
            throw new \InvalidArgumentException('Container accepts only ByteStrings');
        }
        parent::push($value);
    }

    public function unshift($value)
    {
        if (! $value instanceof ByteString) {
            throw new \InvalidArgumentException('Container accepts only ByteStrings');
        }
        parent::unshift($value);
    }

    public function offsetSet($index, $value)
    {
        if (! $value instanceof ByteString) {
            throw new \InvalidArgumentException('Container accepts only ByteStrings');
        }
        parent::offsetSet($index, $value);
    }
}
