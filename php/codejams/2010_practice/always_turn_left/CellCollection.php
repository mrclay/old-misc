<?php

class CellCollection {
	protected $_c = array();
	
	public function get($x, $y, $default = 0)
	{
		return isset($this->_c["$x,$y"])
			? $this->_c["$x,$y"]
			: $default;
	}
	
	public function set($x, $y, $value)
	{
		$this->_c["$x,$y"] = $value;
	}
	
	public function draw()
	{
		// find maxima/minima
		$keys = array_keys($this->_c);
		$numKeys = count($keys);
		// check first location
		list($minX, $minY) = explode(',', $keys[0]);
		$maxX = $minX;
		$maxY = $minY;
		// check rest
		for ($i = 1; $i < $numKeys; $i++) {
			list($x, $y) = explode(',', $keys[$i]);
			$minX = min($minX, $x);
			$minY = min($minY, $y);
			$maxX = max($maxX, $x);
			$maxX = max($maxX, $x);
		}
		// maze was guaranteed to be rectagular
		for ($y = $maxY; $y >= $minY; $y--) {
			for ($x = $minX; $x <= $maxX; $x++) {
				echo dechex($this->_c["$x,$y"]);
			}
			echo "\n";
		}
	}
}
