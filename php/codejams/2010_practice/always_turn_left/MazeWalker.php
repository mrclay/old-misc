<?php

class MazeWalker {

    // directions
    const UP = 0;
    const RIGHT = 1;
    const LEFT = 3;
    const DOWN = 2;

    const START_DIR = 2;

    // direction deltas
    const WALK = '0';
    const TURN_LEFT = '3';
    const TURN_RIGHT = '1';
    const TURN_AROUND = '2';

    /**
     * @var Maze
     */
    public $maze;

	public $direction = 0;
	public $x = 0;
	public $y = 0;

    /**
     * @var array[] vectors for up, right, down, left
     */
    protected static $vectors = array(array(0, 1), array(1, 0), array(0, -1), array(-1, 0));

    protected static $exitBits = array(1, 8, 2, 4);

	public function __construct(Maze $maze)
	{
		$this->maze = $maze;
		$this->direction = self::START_DIR;
		$this->x = 0;
		$this->y = 0;
	}
	
	public function explore($path)
	{
		// we've already walked in
		$path = substr($path, 1);
		// parse path into movements
		$path = str_replace(
			 array('RRW',             'RW',              'LW',             'W')
			,array(self::TURN_AROUND, self::TURN_RIGHT , self::TURN_LEFT , self::WALK)
			,$path
		);
		// make all movements
		for ($i = 0, $l = strlen($path); $i < $l; $i++) {
			$this->performMove((int)$path[$i]);
		}
		// turn around and re-enter
		$this->direction = ($this->direction + 2) % 4;
		$this->walk();
	}

    protected function walk()
    {
        $this->x += self::$vectors[$this->direction][0];
        $this->y += self::$vectors[$this->direction][1];
    }

    /**
     * @param int $delta
     */
    protected function performMove($delta)
	{
		static $dirDeltas = array(0, 1, 2, 3);
		static $exitRelativeDirs = array(array(0, 2), array(1, 2), array(2), array(3, 2));
		static $exitCounts = array(2, 2, 1, 2);

		// mark exits
		for ($i = 0; $i < $exitCounts[$delta]; $i++) {
			// determine direction of exit
			$exitDir = ($this->direction + $exitRelativeDirs[$delta][$i]) % 4;
			// mark cell by OR
			$oldCellExits = $this->maze->get($this->x, $this->y);
			$newCellExits = $oldCellExits | self::$exitBits[$exitDir];
			$this->maze->set($this->x, $this->y, $newCellExits);
		}
		// turn & walk
		$this->direction = ($this->direction + $dirDeltas[$delta]) % 4;
		$this->walk();
	}
}
