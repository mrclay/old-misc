<?php

/**
 * A one-day experiment in writing my own clone of SimpleTest: June 2010
 *
 * @author Steve Clay
 * @link http://mrclay.org/
 */
class MrClay_Minitest {
    public static function runOne($className, MrClay_Minitest_CaseRenderer $renderer = null)
    {
        $case = new $className();
        $case->run($renderer);
    }

    public static function defaultRenderer(MrClay_Minitest_CaseRenderer $set = null)
    {
        static $renderer = null;
        if ($renderer === null) {
            $renderer = new MrClay_Minitest_CaseRenderer_Html();
        }
        if ($set !== null) {
            $renderer = $set;
        }
        return $renderer;
    }

    public static function register(MrClay_Minitest_Case $case = null, $directlyRunnable = true, MrClay_Minitest_CaseRenderer $renderer = null)
    {
        if ($directlyRunnable && basename($_SERVER['SCRIPT_NAME']) == get_class($case) . '.php') {
            return $case->run($renderer);
        }
        self::$_registeredCases[] = $case;
    }

    public static function runRegistered(MrClay_Minitest_CaseRenderer $renderer = null)
    {
        if (! $renderer) {
            $renderer = self::defaultRenderer();
        }
        foreach (self::$_registeredCases as $case) {
            $case->run($renderer);
        }
    }

    protected static $_registeredCases = array();
}


class MrClay_Minitest_Case {
    public $title = 'Untitled';
    const UNTITLED = 'Untitled';

    public function  __construct($title = MrClay_Minitest_Case::UNTITLED) {
        if ($title !== self::UNTITLED) {
            $this->title = $title;
        }
        if ($this->title === self::UNTITLED) {
            $className = get_class($this);
            if (preg_match('@^test_?(.+)@i', $className, $m)) {
                $this->title = $m[1];
            }
        }
    }

    public function setRenderer(MrClay_Minitest_CaseRenderer $renderer)
    {
        $this->_renderer = $renderer;
    }

    protected function _verifyHasRenderer()
    {
        if (! $this->_renderer) {
            $this->_renderer = MrClay_Minitest::defaultRenderer();
        }
    }

    public function run(MrClay_Minitest_CaseRenderer $renderer = null)
    {
        $this->_renderer = $renderer;
        $this->_verifyHasRenderer();
        $this->heading("Testing: <span class=caseTitle>{$this->title}</span>");
        if (method_exists($this, 'before')) {
            $this->_callMethod('before');
        }
        $methods = get_class_methods($this);
        foreach ($methods as $method) {
            if (0 === strpos($method, 'test_')) {
                $this->_callMethod($method);
            }
        }
        if (method_exists($this, 'after')) {
            $this->_callMethod('after');
        }
        $this->_renderer->showFooter();
    }

    protected function _callMethod($method)
    {
        $this->_renderer->setMethodName($method);
        try {
            $this->$method();
        } catch (Exception $e) {
            $this->_renderer->showException($e);
        }
    }

    public function assertTrue($cond, $msgHtml = '')
    {
        return $this->assertCustom('MrClay_Minitest_Assertion_True', array($cond), $msgHtml);
    }

    public function assertIdentical($actual, $expected, $msgHtml = '')
    {
        return $this->assertCustom('MrClay_Minitest_Assertion_Identical', array($actual, $expected), $msgHtml);
    }

    public function assertPattern($pattern, $subject, $msgHtml = '')
    {
        return $this->assertCustom('MrClay_Minitest_Assertion_Pattern', array($pattern, $subject), $msgHtml);
    }

    public function note($html)
    {
        $this->_verifyHasRenderer();
        $this->_renderer->showNote($html);
    }

    public function heading($html)
    {
        $this->_verifyHasRenderer();
        $this->_renderer->showHeading($html);
    }

    public function assertCustom($className, $args = array(), $msgHtml = '')
    {
        $this->_verifyHasRenderer();
        $stack = debug_backtrace();
        $file = $stack[1]['file'];
        $line = $stack[1]['line'];
        $assertion = null;
        if (class_exists($className)) {
            $assertion = new $className($args, $msgHtml, $file, $line);
            return $this->_renderer->showAssertion($assertion);
        }
        throw new Exception('MrClay_Minitest: No Assertion');
    }

    protected $_renderer = null;
}

// base class
class MrClay_Minitest_Assertion {
    protected $_passed = null;
    protected $_args = array();
    protected $_desc = '';

    public $file;
    public $line;

    public function  __construct($args = array(), $desc = '', $file = '', $line = '')
    {
        $this->_args = $args;
        $this->_desc = $desc;
        $this->file = $file;
        $this->line = $line;
    }

    public function passed()
    {
        if ($this->_passed === null) {
            $this->_passed = $this->test();
        }
        return $this->_passed;
    }

    public function test()
    {
        return false;
    }

    public function getDesc()
    {
        return $this->_desc;
    }
}

class MrClay_Minitest_Assertion_True extends MrClay_Minitest_Assertion {
    public function test()
    {
        return (bool) $this->_args[0];
    }
}

class MrClay_Minitest_Assertion_Identical extends MrClay_Minitest_Assertion {
    public function test()
    {
        return ($this->_args[0] === $this->_args[1]);
    }
}

class MrClay_Minitest_Assertion_Pattern extends MrClay_Minitest_Assertion {
    public function test()
    {
        return preg_match($this->_args[0], $this->_args[1]);
    }
}

abstract class MrClay_Minitest_CaseRenderer {
    protected $_passes = 0;
    protected $_runs = 0;
    protected $_prefix = '';
    protected $_headerSent = false;
    protected $_headingId = 0;
    protected $_allPassedSinceHeading = false;
    protected $_methodName = '';

    public function __construct($prefix = '')
    {
        $this->_prefix = $prefix;
    }

    public function output($output)
    {
        echo $output;
        flush();
    }

    public function setMethodName($methodName)
    {
        $this->_methodName = $methodName;
        $this->showNote("<code>$methodName()</code>");
    }

    final public function showHeader()
    {
        if ($this->_headerSent) {
            return;
        }
        $this->output($this->_renderHeader());
        $this->_headerSent = true;
    }

    final protected function _verifyHeaderSent()
    {
        if (! $this->_headerSent) {
            $this->showHeader();
        }
    }

    final public function showHeading($html)
    {
        $this->_headingId += 1;
        $this->_verifyHeaderSent();
        $this->output($this->_renderHeading($html));
        $this->_allPassedSinceHeading = true;
    }

    final public function showNote($html)
    {
        $this->_verifyHeaderSent();
        $this->output($this->_renderNote($html));
    }

    final public function showAssertion(MrClay_Minitest_Assertion $assertion)
    {
        $this->_verifyHeaderSent();
        $didPass = $this->_updateStats($assertion->passed());
        $desc = $assertion->getDesc();
        $line = $assertion->line;
        $file = $assertion->file;
        $assertionType = get_class($assertion);
        if (0 === strpos($assertionType, 'MrClay_Minitest_Assertion_')) {
            $assertionType = 'assert' . substr($assertionType, 26);
        }
        $this->output($this->_renderAssertion($didPass, $desc, $file, $line, $assertionType));
        return $didPass;
    }

    final public function showException($e)
    {
        $this->_verifyHeaderSent();
        $this->output($this->_renderException($e));
    }

    final public function showFooter()
    {
        $this->output($this->_renderFooter());
    }

    protected function _updateStats($didPass = true)
    {
        if ($didPass) {
            $this->_passes += 1;
        } else {
            $this->_allPassedSinceHeading = false;
        }
        $this->_runs += 1;
        return $didPass;
    }

    public function getFailures()
    {
        $value = ($this->_runs - $this->_passes);
        $unit = ($value === 1) ? 'failure' : 'failures';
        return "$value $unit";
    }

    abstract protected function _renderHeader();

    abstract protected function _renderHeading($html);

    abstract protected function _renderAssertion($didPass, $desc, $file, $line, $assertionType);

    abstract protected function _renderNote($html);

    abstract protected function _renderException(Exception $e);

    abstract protected function _renderFooter();

    abstract protected function _renderAllPassedSinceHeading();

}


class MrClay_Minitest_CaseRenderer_Text extends MrClay_Minitest_CaseRenderer {
    protected function _renderHeader()
    {
        if (! headers_sent()) {
            header('Content-Type: text/plain;charset=utf-8');
        }
    }
    protected function _renderHeading($html)
    {
        return "#\n# " . strip_tags($html) ."\n#\n";
    }
    protected function _renderAssertion($didPass, $desc, $file, $line, $assertionType)
    {
        $result = $didPass ? 'PASS' : '!FAIL';
        $stats = sprintf("(%s/%s or %0.0f%% passed)",
             $this->_passes, $this->_runs, ($this->_passes/$this->_runs*100)
        );
        if (! $desc) {
            $desc = "{$assertionType}() in " . basename($file) . ":$line";
        }
        $desc = strip_tags($desc);
        if (! $didPass) {
            $desc .= "(in $file:$line)\t";
        }
        return "{$result}\t{$this->_prefix}{$desc}\t{$stats}\n";
    }
    protected function _renderNote($html)
    {
        return "#\t" . strip_tags($html) . "\n";
    }
    protected function _renderException(Exception $e)
    {
        return "\n!EXCEPTION\t{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}.\n\n";
    }
    protected function _renderFooter()
    {
        return "#\n# Test case completed with {$this->getFailures()}.\n";
    }
    protected function _renderAllPassedSinceHeading()
    {
        return "";
    }
}

class MrClay_Minitest_CaseRenderer_Html extends MrClay_Minitest_CaseRenderer {
    protected function _renderHeader()
    {
        if (! headers_sent()) {
            header('Content-Type: text/html;charset=utf-8');
        }
        ob_start();
        ?><!doctype html><title>MrClay_Minitest Test Case</title>
<style>
    span.pass {color:#060;}
    span.pass, strong.fail, strong.note {font:normal 1.5em monospace; margin-right: .4em}
    strong.fail, strong.note {font-weight: 900}
    strong.note {color:#999}
    p {margin:.5em 0}
    div.fail {background:#c00; color:#fff; padding: .3em; margin: .2em 0 .3em;}
    .stats, .trace {color:#666; font-size: .9em; margin-left: .6em}
    div.fail .stats {color:#fcc}
    h2 {color:#999}
    h2 .failed {display: none}
    h2 .caseTitle {font-family: monospace; color:#000}
    .allPass .failed {display: inline; color:#fff; background:#060; padding: .5em 2em;
                  font: italic 1em monospace; font-variant: small-caps; margin-left: .5em}
</style>
<script>
function MrClay_Minitest_allPassed(id) {
    var o = window.onload;
    window.onload = function () {
        o && o();
        document.getElementById(id).className = 'allPass';
    };
}
</script>
        <body>
        <?php
        return ob_get_clean();
    }
    protected function _renderHeading($html)
    {
        $ret = $this->_renderAllPassedSinceHeading();
        return "$ret<h2 id=h{$this->_headingId}>$html <span class=failed>All Passed!</span></h2>";
    }
    protected function _renderAssertion($didPass, $desc, $file, $line, $assertionType)
    {
        $result = $didPass
            ? '<span class=pass>PASS</span>'
            : '<strong class=fail>!FAIL</strong>';
        $lineClass = $didPass ? 'pass' : 'fail';
        $stats = sprintf("%s/%s or %0.0f%% passed",
            $this->_passes, $this->_runs, ($this->_passes/$this->_runs*100)
        );

        if (! $desc) {
            $desc = "<code>{$assertionType}()</code> in <code>" . basename($file) . "</code> line $line";
        }
        if (! $didPass) {
            $desc .= " (in $file:$line) ";
        }

        return "<div class={$lineClass}>{$result} {$desc} <span class=stats>{$stats}</span></div>";
    }
    protected function _renderNote($html)
    {
        return "<p><strong class=note>NOTE</strong> $html</p>";
    }
    protected function _renderException(Exception $e)
    {
        $msg = htmlspecialchars($e->getMessage(), ENT_NOQUOTES, 'UTF-8');
        return "<p><strong class=fail>!EXCEPTION</strong> $msg <small class=trace>"
            . "{$e->getFile()}:{$e->getLine()}</small></p>";
    }
    protected function _renderFooter()
    {
        $ret = $this->_renderAllPassedSinceHeading();
        $failures = $this->getFailures();
        list($num) = explode(' ', $failures);
        return $ret . "<hr><p>Test case completed with $failures.</p>";
    }
    protected function _renderAllPassedSinceHeading()
    {
        if ($this->_headingId && $this->_allPassedSinceHeading) {
            return "<script>MrClay_Minitest_allPassed('h{$this->_headingId}');</script>";
        }
        return "";
    }
}

// Echoes "1" only if all tests pass. Useful for remote sanity/ajax testing...
class MrClay_Minitest_CaseRenderer_AllPassed extends MrClay_Minitest_CaseRenderer {
    protected function _renderHeader()
    {
        return "";
    }
    protected function _renderHeading($html)
    {
        return "";
    }
    protected function _renderAssertion($didPass, $desc, $file, $line, $assertionType)
    {
        return "";
    }
    protected function _renderNote($html)
    {
        return "";
    }
    protected function _renderException(Exception $e)
    {
        return "";
    }
    protected function _renderFooter()
    {
        return ($this->_passes == $this->_runs)
            ? "1"
            : "";
    }
    protected function _renderAllPassedSinceHeading()
    {
        return "";
    }
}