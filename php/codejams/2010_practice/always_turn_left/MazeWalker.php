<?php

class MazeWalker {

	public $maze = null;
	public $dir = 0;
	public $x = 0;
	public $y = 0;
	
	protected static $_vectors = array(array(0, 1), array(1, 0), array(0, -1), array(-1, 0));
	protected static $_exitBits = array(1, 8, 2, 4);
	const START_DIR = 2;
	
	public function __construct(CellCollection $cc)
	{
		$this->maze = $cc;
		$this->dir = self::START_DIR;
		$this->x = 0;
		$this->y = 0;
	}
	
	public function walk()
	{
		$this->x += self::$_vectors[$this->dir][0];
		$this->y += self::$_vectors[$this->dir][1];
	}
	
	public function explore($path)
	{
		// we've already walked in
		$path = substr($path, 1);
		// parse path into movements
		$path = str_replace(
			 array('RRW', 'RW', 'LW', 'W')
			,array('2'  , '1' , '3' , '0')
			,$path
		);
		// make all movements
		for ($i = 0, $l = strlen($path); $i < $l; $i++) {
			$this->performMove((int)$path[$i]);
		}
		// turn around and re-enter
		$this->dir = ($this->dir + 2) % 4;
		$this->walk();
	}
	
	public function performMove($m)
	{
		static $dirDeltas = array(0, 1, 2, 3);
		static $exitRelativeDirs = array(array(0, 2), array(1, 2), array(2), array(3, 2));
		static $exitCounts = array(2, 2, 1, 2);
		// mark exits
		for ($i = 0; $i < $exitCounts[$m]; $i++) {
			// determine direction of exit
			$exitDir = ($this->dir + $exitRelativeDirs[$m][$i]) % 4;
			// mark cell
			$oldCellExits = $this->maze->get($this->x, $this->y);
			$newCellExits = $oldCellExits | self::$_exitBits[$exitDir];
			$this->maze->set($this->x, $this->y, $newCellExits);
		}
		// turn & walk
		$this->dir = ($this->dir + $dirDeltas[$m]) % 4;
		$this->walk();
	}
}
