<?php

class Point {
    public $x;
    public $y;
    public function __construct($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    // points assumed to have integer coordinates
    public function equals($p)
    {
        return (($this->x == $p->x) && ($this->y == $p->y));
    }
    
    public function distanceTo($p)
    {
        return $this->equals($p)
            ? 0
            : abs(sqrt( pow($p->x - $this->x, 2) + pow($p->y - $this->y, 2)));
    }
    
    public function angleTo($p)
    {
        return fmod(2 * M_PI + atan2($p->y - $this->y, $p->x - $this->x), 2 * M_PI);
    }
    
    public function acuteAngleBetween($p1, $p2)
    {
        $a =   atan2($p2->y - $this->y, $p2->x - $this->x)
             - atan2($p1->y - $this->y, $p1->x - $this->x);
        $a = fmod(2 * M_PI + $a, 2 * M_PI);
        switch (true) {
            case ($a < M_PI_2)    : return $a;
            case ($a < M_PI)      : return M_PI - $a;
            case ($a < 3 * M_PI_2): return $a - M_PI;
            default               : return 2 * M_PI - $a;
        }
    }
}
