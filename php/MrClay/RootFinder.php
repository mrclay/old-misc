<?php

/**
 * Use Newton's Method to find the root of a function
 * 
 * E.g. estimate square root of 7.
 * Find x | y(x) = x^2 - 7 = 0.
 * 
 * <code>
 * class FunctionY {
 *     // y
 *     public function call($x) {
 *         return pow($x, 2) - 7;
 *     }
 *     // dy/dx
 *     public function callDerivative($x) {
 *         return 2 * $x;
 *     }
 * }
 * $rf = new MrClay_RootFinder();
 * echo $rf->findRoot(new FunctionY) . "\n";
 * echo $rf->error . "\n";
 * print_r($rf->x); // all guesses
 * </code>
 *
 * @link http://en.wikipedia.org/wiki/Newton's_method
 *
 * @author Steve Clay <steve@mrclay.org>
 * @license http://www.opensource.org/licenses/mit-license.php  MIT License
 */
class MrClay_RootFinder {
    public $error = null;
    public $x = array();

    /**
     * Find an x such that $f->call($x) is within $epsilon of 0
     *
     * @param mixed $f function object with methods call($x), callDerivative($x)
     * @param float $x0 (optional) initial guess of root
     * @param float $epsilon (optional) acceptable distance from 0
     * @param int $maxIterations (optional)
     * @return float
     */
    public function findRoot($f, $x0 = 0, $epsilon = 0.00001, $maxIterations = 50)
    {
        $this->error = null;
        if (0 == $f->callDerivative($x0)) {
            $x0 = 1;
        }
        $this->x = array($x0);
        $i = 0;
        $errorPrev = null;
        
        do {
            $denominator = $f->callDerivative($this->x[$i]);
            if (0 == $denominator) {
                throw new Exception(
                    'Derivative at ' . $this->x[$i] . ' returned zero. Cannot continue');
            }
            $this->x[$i + 1] = $this->x[$i] - $f->call($this->x[$i]) / $denominator;
            $this->error = abs($this->x[$i + 1] - $this->x[$i]);
            
            if ($errorPrev !== null && $errorPrev <= $this->error) {
                // error is not decreasing, send last x
                $this->error = $errorPrev;
                return $this->x[$i];
            } elseif ($this->error < $epsilon) {
                // close enough
                return $this->x[$i + 1];
            } else {
                // prepare next iteration
                $i++;
                $errorPrev = $this->error;
            }
        } while ($i < $maxIterations);
        
        return $this->x[$i];
    }
}
