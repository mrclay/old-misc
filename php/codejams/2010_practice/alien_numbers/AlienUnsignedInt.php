<?php

/**
 * Unsigned integer with unlimited size and arbitrary (non-space) chars as digits
 *
 * Written 2010, Cleaned up 2013
 */
class AlienUnsignedInt {

    const UP = 1;
    const DOWN = -1;

    /**
     * @var string
     */
    public $value;
    
    /**
     * @var string
     */
    protected $digits;

    /**
     * @var int
     */
    protected $numDigits;

    /**
     * @var int[] value for each digit. E.g. in hex, "9" => 9, "a" => 10...
     */
    protected $digitValues = array();

    /**
     * @var int[] value added by incrementing each digit (up to PHP_INT_MAX).
     *            E.g. for hex this would be 2 => 16^1, 3 => 16^2, ...
     */
    protected $positionValues = array(); // int values of place digits

    /**
     * @param string $number Number using given digits.
     * @param string $digits
     */
    public function __construct($number, $digits = '0123456789') {
        $this->value = (string)$number;
        $this->digits = $digits;
        $this->digitValues = array_flip(str_split($digits));
        $this->numDigits = strlen($digits);

        // generate lookup table
        $i = 1;
        while (true) {
            $v = pow($this->numDigits, $i);
            if (! is_int($v) || $v < 1) {
                // we're beyond ints
                break;
            }
            $this->positionValues[$i + 1] = $v;
            $i++;
        }
    }
    
    public function inc() {
        $this->incDecAtIndex(self::UP);
    }

    public function dec() {
        if ($this->value === $this->digits[1]) {
            $this->value = $this->digits[0];
            return;
        }
        $this->incDecAtIndex(self::DOWN);
    }
    
    /**
     * Increment/decrement the digit at a particular position (significantly faster than inc/dec)
     *
     * @param int $direction UP or DOWN
     * @param int $position E.g. 1 for 1s, 2 for 10s column, 3 for 100s column
     */
    public function incDecAtPosition($direction, $position) {
        $len = strlen($this->value);
        $stringIndex = $len - $position;
        if ($direction === self::UP && $stringIndex < 0) {
            // pad with the 0-value digit
            $this->value = str_pad($this->value, $position, $this->digits[0], STR_PAD_LEFT);
            // recompute the index
            $len = strlen($this->value);
            $stringIndex = $len - $position;
        }
        $this->incDecAtIndex($direction, $stringIndex);
        if ($direction === self::DOWN) {
            // trim leading 0-value digits
            $this->value = ltrim($this->value, $this->digits[0]);
            if ($this->value === '') {
                $this->value = $this->digits[0];
            }
        }
    }
    
    /**
     * Get the largest value (and its position) that can be subtracted by decrementing
     * a position value.
     *
     * E.g. Decimal 86 this would be [10, 2], since 10 can be removed by decrementing the
     * second position.
     *
     * @return array [int value, int position] or [false, false]
     */
    public function getLargestCachedPositionValue() {
        $position = strlen($this->value);
        while ($position) {
            if (isset($this->positionValues[$position])) {
                return array($this->positionValues[$position], $position);
            }
            $position--;
        }
        return array(false, false);
    }
    
    // check this before dec()!
    public function isZero() {
        return ($this->value === $this->digits[0]);
    }
    
    /**
     * Get a value-equivalent AlienUnsignedInt
     *
     * @param string $newDigits Digits of the new object
     * @return AlienUnsignedInt
     */
    public function convert($newDigits) {
        $dest = new self($newDigits[0], $newDigits);
        $src = clone $this;

        // try optimized transfers
        while (true) {
            list($value, $position) = $src->getLargestCachedPositionValue();
            if ($value === false) {
                break;
            }
            // transfer value from $src, add to $dest
            $src->incDecAtPosition(self::DOWN, $position);
            $dest->add($value);
        }
        // give up and transfer 1 at a time
        while (! $src->isZero()) {
            $src->dec();
            $dest->inc();
        }
        return $dest;
    }
    
    /**
     * Add a value to the number
     *
     * @param int $value
     */
    public function add($value) {
        // avoids needless recursion
        do {
            $value = $this->addSome($value);
        } while ($value);
    }

    /**
     * Add a portion of value and return the value left to be added.
     *
     * @param int $value
     * @return int The remainder left to be added
     */
    protected function addSome($value) {
        if ($value < $this->positionValues[2]) {
            // too small to add a place
            for ($i = 0; $i < $value; $i++) {
                $this->inc();
            }
            return 0;
        }
        // we shift a place as necessary then add the remainder

        // find largest $p such that $placeValues[$p] <= int
        // e.g. if value = 1005, and the system were hex, the largest value would
        // be 256 at position 3
        $position = count($this->positionValues);
        while ($this->positionValues[$position] > $value) {
            $position--;
        }

        $factor = $this->positionValues[$position];

        // if value were 1005 and factor 256, this would mean we should
        // increment the 3rd position 3 times (768)...
        $numPlaceIncrements = floor($value / $factor);
        // ...leaving 237 to increment.
        $remainder = $value % $factor; // 237

        for ($i = 0; $i < $numPlaceIncrements; $i++) {
            $this->incDecAtPosition(self::UP, $position);
        }
        return $remainder;
    }
    
    public function __toString() {
        return $this->value;
    }
    
    /**
     * Find the next digit up or down. This will return the digit and whether
     * or not the position wrapped around.
     *
     * @param string $digit The digit to be nudged
     * @param int $direction UP or DOWN
     * @return array [string $new_digit, bool $wrap_occurred]
     */
    protected function incDecDigit($digit, $direction) {
        $val = $this->digitValues[$digit] + $direction;
        $wrap_occurred = ($val < 0 || $val == $this->numDigits);
        $val = ($val + $this->numDigits) % $this->numDigits;
        return array($this->digits[$val], $wrap_occurred);
    }

    /**
     * Increment/decrement the digit at a particular string index in the value
     *
     * @param int $direction UP or DOWN
     * @param int|null $stringIndex If null is given, this will be the index of the
     *                              last digit. E.g. 2 if the value is "123"
     */
    protected function incDecAtIndex($direction, $stringIndex = null) {
        if (null === $stringIndex) {
            $stringIndex = strlen($this->value) - 1;
        }
        if ($stringIndex === -1 && $direction === self::UP) {
            // place a 1 digit in the new position on the left
            $this->value = $this->digits[1] . $this->value;
            return;
        }

        $digit = $this->value[$stringIndex];
        list($digit, $wrap_occurred) = $this->incDecDigit($digit, $direction);

        $this->value[$stringIndex] = $digit;
        if ($direction === self::DOWN && $stringIndex === 0 && $digit === $this->digits[0]) {
            // remove leading 0
            $this->value = substr($this->value, 1);
            return;
        }
        if ($wrap_occurred) {
            $this->incDecAtIndex($direction, $stringIndex - 1);
        }
    }
}
