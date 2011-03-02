<?php

require_once dirname(__FILE__) . '/../../../../php/MrClay/AutoP/WordPress.php';
require_once dirname(__FILE__) . '/../../../../php/MrClay/AutoP.php';

class MrClay_AutoP_WordPressTest extends PHPUnit_Framework_TestCase {

    /**
     * @var MrClay_AutoP_WordPress
     */
    protected $_autopWP;

    /**
     * @var MrClay_AutoP
     */
    protected $_autop;

    protected function setUp() {
        $this->_autopWP = new MrClay_AutoP_WordPress();
        $this->_autop = new MrClay_AutoP();
    }
    
    /**
     * @dataProvider provider
     */
    public function testInOut($test, $in, $exp)
    {
        $out = $this->_autopWP->process($in);
        $this->assertEquals($exp, $out, "Equality case {$test}");
    }

    public function provider()
    {
        $d = dir(__DIR__);
        $tests = array();
        while (false !== ($entry = $d->read())) {
            if (preg_match('/^([a-z\\-]+)\.in\.html$/i', $entry, $m)) {
                $tests[] = $m[1];
            }
        }
//$tests = array("4"); // limit to a single test
        $data = array();
        foreach ($tests as $test) {
            $data[] = array(
                $test,
                file_get_contents($d->path . '/' . "{$test}.in.html"),
                file_get_contents($d->path . '/' . "{$test}.exp.html"),
            );
        }
        return $data;
    }
}
