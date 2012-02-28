<?php

/**
 * Wet Bulb target function (for Newton's method)
 *
 * From http://en.wikipedia.org/wiki/Dew_point#Closer_approximation
 * e = ew - p(T - Tw) .00066 (1 + .00115 Tw)
 * So...
 * 0 = ew - e - p(T - Tw) .00066 (1 + .00115 Tw)
 *
 * call() evaluates ew - e - bp(T - Tw) .00066 (1 + .00115 Tw) at Tw
 * callDerivative() evaluates the derivative w/r/t Tw at Tw
 *
 * @link http://en.wikipedia.org/wiki/Wet-bulb_temperature
 *
 * @author Steve Clay <steve@mrclay.org>
 * @license http://www.opensource.org/licenses/mit-license.php  MIT License
 */
class MrClay_RootFinder_WetBulb {
    public $t2m = null;
    public $vp = null;
    public $bp = null;

    /**
     * @param float $t2m			air temp (C)
     * @param float $vp				water vapor pressure (mb)
     * @param float $bp				optional station pressure (mb)
     */
    public function __construct($t2m, $vp, $bp = 1013.0)
    {
        $this->t2m = $t2m;
        $this->vp = $vp;
        $this->bp = $bp;
    }

    // http://en.wikipedia.org/wiki/Dew_point#Closer_approximation
    public function call($Tw)
    {
        $T = $this->t2m;
        $e = $this->vp;
        $p = $this->bp;
        $ew = $this->e($Tw);
        return 
            $ew - $e - $p * ($T - $Tw) * 0.00066 * (1 + 0.00115 * $Tw);
    }

    public function callDerivative($Tw)
    {
        $T = $this->t2m;
        $e = $this->vp;
        $p = $this->bp;
        return 
            $this->de_dt($Tw)
            + $p * 0.00066 * (1 - 0.00115 * ($T - 2 * $Tw));
    }

    /**
     * Calculate saturation vapor pressure using Bolton equation
     *
     * @param float $t			air temperature (C)
     * @return float			saturation vapor pressure (mb)
     * @link http://cires.colorado.edu/~voemel/vp.html
     */
    public function e($t)
    {
        return 6.112 * exp(17.67 * $t / ($t + 243.5));
    }

    /**
     * de/dt evaluated at point $x
     *
     * @param float $t			air temperature (C)
     * @return float			saturation vapor pressure (mb)
     * @link http://cires.colorado.edu/~voemel/vp.html
     */
    public function de_dt($t)
    {
        return 6.112                                    // constant
               * (17.67 * 243.5) / pow($t + 243.5, 2)   // d/dx(exponent)
               * exp(17.67 * $t / ($t + 243.5));        // exponent
    }
}
