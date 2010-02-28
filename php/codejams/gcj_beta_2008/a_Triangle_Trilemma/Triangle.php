<?php

class Triangle {
    public $pts = array();
    
    protected function __construct() {}
    
    // returns Triangle or false
    public static function factory($p1, $p2, $p3)
    {
        if ($p1->equals($p2) || $p1->equals($p3) || $p2->equals($p3)) {
            return false;
        }
        // test if colinear
        $a = array($p1->angleTo($p2), $p1->angleTo($p3));
        sort($a);
        if (self::withinEps($a[0], $a[1])
            || self::withinEps($a[1] - M_PI, $a[0])
        ) {
            return false;
        }
        $tri = new Triangle();
        $tri->pts = array($p1, $p2, $p3);
        return $tri;
    }
    
    public function describe()
    {
        // side lengths (and rounded)
        $s[] = $this->pts[0]->distanceTo($this->pts[1]);
        $s[] = $this->pts[1]->distanceTo($this->pts[2]);
        $s[] = $this->pts[2]->distanceTo($this->pts[0]);
        // find longest side
        $longest = 0;
        for ($i = 0; $i < 3; $i++) {
            $sr[$i] = round($s[$i], 4);
            if ($s[$i] > $s[$longest]) {
                $longest = $i;
            }
        }
        $type1 = (count(array_unique($sr)) == 3) // all sides differ
            ? 'scalene'
            : 'isosceles';
        // longest side must link acute vertices. measure them and subtract from PI
        // points on longest side
        $p1 = $this->pts[$longest];
        $p2 = $this->pts[($longest + 1) % 3];
        // point opposite longest side
        $pa = $this->pts[($longest + 2) % 3];
        $angleP1 = $p1->acuteAngleBetween($pa, $p2);
        $angleP2 = $p2->acuteAngleBetween($pa, $p1);
        $angle = M_PI - $angleP1 - $angleP2;
        switch (true) {
            case self::withinEps($angle, M_PI_2): return "$type1 right triangle";
            case ($angle > M_PI_2)              : return "$type1 obtuse triangle";
            default                             : return "$type1 acute triangle";
        }
    }
    
    public static function withinEps($v1, $v2, $eps = 0.0001) {
        return abs($v1 - $v2) < $eps;
    }
}
