<?php

require_once dirname(__FILE__) . '/../../../php/MrClay/AutoP.php';

class MrClay_AutoPTest extends PHPUnit_Framework_TestCase {

    /**
     * @var MrClay_AutoP
     */
    protected $_autop;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->_autop = new MrClay_AutoP;
    }

    
    public function testDomRoundtrip()
    {
        $d = dir(__DIR__ . '/AutoP');
        $in = file_get_contents($d->path . "/domdoc_in.html");
        $exp = file_get_contents($d->path . "/domdoc_exp.html");

        //$in = implode("\n", array_values(get_html_translation_table(HTML_ENTITIES, ENT_NOQUOTES))) . $in;

        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML("<html><meta http-equiv='content-type' content='text/html; charset=utf-8'><body>"
                . $in . '</body></html>');
        $serialized = $doc->saveHTML();
        list(,$out) = explode('<body>', $serialized, 2);
        list($out) = explode('</body>', $out, 2);

        $this->assertEquals($exp, $out, "DOMDocument's parsing/serialization roundtrip");
    }

    
    public function testInOut()
    {
        $tests = array();
        $d = dir(__DIR__ . '/AutoP');
        while (false !== ($entry = $d->read())) {
            if (preg_match('/^(\\d+)_in\.html$/', $entry, $m)) {
                $tests[] = $m[1];
            }
        }

//$tests = array("4"); // limit to a single test

        foreach ($tests as $test) {
            $in = file_get_contents($d->path . '/' . "{$test}_in.html");
            $exp = file_get_contents($d->path . '/' . "{$test}_exp.html");

            $out = $this->_autop->process($in);
            
//die($out);

            $this->assertEquals($exp, $out, "Equality case {$test}");
        }
    }
}
