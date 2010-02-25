<?php

/**
 * Unsigned integer with unlimited size and arbitrary (non-space) chars as digits
 */
class AlienUnsignedInt {
    public $n; // the number (string)
    
    // these should really be in an AlienNumberSystem object
    // so they could be shared between numbers
    protected $d; // string of digits
    protected $l = array(); // digits => values. E.g. $hex->l['a'] = 10
    protected $mod; // num digits in system
    protected $placeValues = array(); // int values of place digits (up to PHP_INT_MAX)
    
    public function __construct($number = null, $digits = '0123456789') {
        if (! is_string($number)) {
            $number = $digits[0];
        }
        $this->n = (string)$number;
        $this->d = $digits;
        $this->l = array_flip(str_split($digits));
        $this->mod = strlen($digits);
        // generate placeValues lookup table
        $i = 1;
        while (true) {
            $v = pow($this->mod, $i);
            if (! is_int($v) || $v < 1) {
                // we're beyond ints
                break;
            }
            $this->placeValues[$i + 1] = $v;
            $i++;
        }
    }
    
    public function inc() {
        $this->_incDec();
    }

    public function dec() {
        if ($this->n === $this->d[1]) {
            $this->n = $this->d[0];
            return;
        }
        $this->_incDec(-1);
    }
    
    // increment/decrement the digit at a place (significantly faster than inc/dec)
    public function incDecPlace($direction /* 1 or -1*/, $place) {
        $len = strlen($this->n);
        $placeIdx = $len - $place;
        if ($direction === 1 && $placeIdx < 0) {
            // pad with zeros
            $this->n = str_pad($this->n, $place, $this->d[0], STR_PAD_LEFT);
            // recompute placeIdx
            $len = strlen($this->n);
            $placeIdx = $len - $place;
        }
        $this->_incDec($direction, $placeIdx);
        if ($direction === -1) {
            $this->n = ltrim($this->n, $this->d[0]);
            if ($this->n === '') {
                $this->n = $this->d[0];
            }
        }
    }
    
    // get largest int value that can be subtracted by decrementind a place value.
    // [int, place] E.g. Decimal 86 this would be [10, 2]
    public function getLargestInt() {
        $place = strlen($this->n);
        while ($place) {
            if (isset($this->placeValues[$place])) {
                return array($this->placeValues[$place], $place);
            }
            $place--;
        }
        return array(false, false);
    }
    
    // check this before dec()!
    public function isZero() {
        return ($this->n === $this->d[0]);
    }
    
    // return equivalent AlienUnsignedInt w/ different digits
    // (optimized based on number system place values within the PHP integers)
    public function convert($newDigits) {
        $dest = new self(null, $newDigits);
        $src = clone $this;
        // try optimized transfers
        while (true) {
            list($int, $place) = $src->getLargestInt();
            if ($int === false) {
                break;
            }
            // remove from $src, add to $dest
            $src->incDecPlace(-1, $place);
            $dest->add($int);
        }
        // naive value by value transfer
        while (! $src->isZero()) {
            $src->dec();
            $dest->inc();
        }
        return $dest;
    }
    
    // add decimal value to number
    // (optimized based on number system place values within the PHP integers)
    public function add($int) {
        if ($int < $this->placeValues[2]) {
            // too small to add a place
            for ($i = 0; $i < $int; $i++) {
                $this->inc();
            }
            return;
        }
        // we shift a place as necessary then add the remainder
        // find largest $p : $placeValues[$p] <= int
        for ($p = count($this->placeValues); $this->placeValues[$p] > $int; $p--) { /* noop */ }
        $factor = $this->placeValues[$p]; // 256, $p = 3, $int = 1005
        $numPlaceIncrements = floor($int / $factor); // 3
        $remainder = $int % $factor; // 237
        for ($i = 0; $i < $numPlaceIncrements; $i++) {
            $this->incDecPlace(1, $p);
        }
        if ($remainder) {
            $this->add($remainder);
        }
    }
    
    public function __toString() {
        return $this->n; 
    }
    
    // [newdigit, didItWrap?]
    protected function _moduloAdd($oldDigit, $direction /* 1 or -1*/) {
        $val = $this->l[$oldDigit] + $direction;
        $wrapped = ($val < 0 || $val == $this->mod);
        $val = ($val + $this->mod) % $this->mod;
        return array($this->d[$val], $wrapped);
    }
    
    protected function _incDec($direction = 1 /* or -1*/, $placeIdx = null) {
        if (null === $placeIdx) {
            $placeIdx = strlen($this->n) - 1;
        }
        if ($placeIdx === -1 && $direction === 1) {
            // add a place
            $this->n = $this->d[1] . $this->n;
            return;
        }
        list($newDigit, $wrapped) = $this->_moduloAdd($this->n[$placeIdx], $direction);
        $this->n[$placeIdx] = $newDigit;
        if ($direction === -1 && $placeIdx === 0 && $newDigit === $this->d[0]) {
            // remove leading 0
            $this->n = substr($this->n, 1);
            return;
        }
        if ($wrapped) {
            $this->_incDec($direction, $placeIdx - 1);
        }
    }
}
