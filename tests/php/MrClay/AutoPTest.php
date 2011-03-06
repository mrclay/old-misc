<?php

require_once dirname(__FILE__) . '/../../../php/MrClay/AutoP.php';

class MrClay_AutoPTest extends PHPUnit_Framework_TestCase {

    /**
     * @var MrClay_AutoP
     */
    protected $_autop;

    protected function setUp() {
        $this->_autop = new MrClay_AutoP;
    }
    
    public function testDomRoundtrip()
    {
        $d = dir(__DIR__ . '/AutoP');
        $in = file_get_contents($d->path . "/domdoc_in.html");
        $exp = file_get_contents($d->path . "/domdoc_exp.html");

        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML("<html><meta http-equiv='content-type' content='text/html; charset=utf-8'><body>"
                . $in . '</body></html>');
        $serialized = $doc->saveHTML();
        list(,$out) = explode('<body>', $serialized, 2);
        list($out) = explode('</body>', $out, 2);

        $this->assertEquals($exp, $out, "DOMDocument's parsing/serialization roundtrip");
    }

    /**
     * @dataProvider provider
     */
    public function testInOut($test, $in, $exp)
    {
        $out = $this->_autop->process($in);
//die($out);
        $this->assertEquals($exp, $out, "Equality case {$test}");
    }

    public function provider()
    {
        $d = dir(__DIR__ . '/AutoP');
        $tests = array();
        while (false !== ($entry = $d->read())) {
            if (preg_match('/^([a-z\\-]+)\.in\.html$/i', $entry, $m)) {
                $tests[] = $m[1];
            }
        }
//$tests = array("typical-post"); // limit to a single test
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
