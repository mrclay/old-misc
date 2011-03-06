<?php

/**
 * Benchmark a piece of code for a minimum amount of time
 *
 * <code>
 * $bench = new MrClay_Bench();
 * do {
 *     // my test code
 * } while ($bench->shouldContinue);
 *
 * var_export($bench->elapsedTimes);
 * </code>
 */
class MrClay_Bench {

    /**
     * Test should be repeated until this many seconds have passed
     *
     * @var float
     */
    public $minSeconds = null;

    /**
     * Test MUST NOT be repeated after this many seconds have passed
     *
     * @var float
     */
    public $maxSeconds = 10;

    /**
     * Test MUST NOT be repeated over this many times
     *
     * @var integer
     */
    public $maxIterations = 10000;

    /**
     * After testing, this will contain total time from reset() to last call of
     * shouldContinue().
     *
     * @var float
     */
    public $benchTime = 0;

    /**
     * Number of tests run
     *
     * @var int
     */
    public $iterationsRun = 0;

    /**
     * Mean number of seconds per test (divided with bcdiv())
     * 
     * @var string (float)
     */
    public $meanTime = 0;

    /**
     * @param float $minSeconds sets property $minSeconds
     */
    public function __construct($minSeconds = 1)
    {
        $this->minSeconds = $minSeconds;
        $this->reset();
    }

    /**
     * Allow starting a new testing loop
     */
    public function reset()
    {
        $this->_isSatisfied = false;
        $this->iterationsRun = 0;
        $this->_timeInitialized = microtime(true);
    }

    /**
     * Should the benchmark continue? Use this as the condition of a do...while
     * loop with a call to your test function in the loop body.
     *
     * @param float $elapsedTime
     * @return bool
     */
    public function shouldContinue()
    {
        if ($this->_isSatisfied) {
            throw new Exception('Tests continued after conditions were satisfied');
        }
        $this->iterationsRun++;
        $this->benchTime = microtime(true) - $this->_timeInitialized;
        if ($this->benchTime > min($this->maxSeconds, $this->minSeconds)
            || $this->iterationsRun >= $this->maxIterations) {
            $this->_isSatisfied = true;
            $this->meanTime = bcdiv($this->benchTime, $this->iterationsRun, 10);
        }
        return ! $this->_isSatisfied;
    }

    protected $_timeInitialized = null;
    protected $_isSatisfied = false;
}

