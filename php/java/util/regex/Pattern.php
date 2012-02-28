<?php

namespace java\util\regex;
use java\lang\StringBuilder;

/**
 * WARNING! This is an attempt to port java.util.regex.Pattern. It's probably full of bugs.
 * Don't use it.
 *
 * @author Steve Clay <steve@mrclay.org>
 * @license http://www.opensource.org/licenses/mit-license.php  MIT License
 */
class Pattern {

    /**
     * @var string PCRE pattern in UTF-8
     */
    protected $pattern = '';

    /**
     * @var int
     */
    private $numGroups = null;

    /**
     * @param StringBuilder $pattern
     */
    protected function __construct(StringBuilder $pattern)
    {
        $this->pattern = $pattern->getBytes();
    }

    /**
     * @param StringBuilder $input
     * @return Matcher
     */
    public function matcher(StringBuilder $input)
    {
        return new Matcher($this, $input);
    }

    /**
     * @param StringBuilder $regex
     * @param StringBuilder $input
     * @return bool
     */
    public function matches(StringBuilder $regex, StringBuilder $input)
    {
        return self::compile($regex)->matcher($input)->matches();
    }

    /**
     * @return int
     */
    public function capturingGroupCount()
    {
        if (null === $this->numGroups) {
            // we're only interested in counting numbered groups
            // explicitly escaped
            $pattern = preg_replace('/\\\\./', '%', $this->pattern);
            // implicitly escaped
            $pattern = preg_replace('/\[[^\]]*\]/', '%', $pattern);
            $this->numGroups = preg_match_all('/\((?!\?)/', $pattern, $m);
        }
        return $this->numGroups;
    }

    /**
     * (renamed due to PHP naming conventions)
     * @return string get pattern as UTF-8 bytes
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * @param StringBuilder $input
     * @param int $limit
     * @return array of StringBuilder
     */
    public function split(StringBuilder $input, $limit = 0)
    {
        if ($limit < 0) {
            $limit = null;
        }
        $r = array();
        foreach(preg_split($this->pattern, $input->getBytes(), $limit) as $str) {
            $r[] = new StringBuilder($str);
        }
        if ($limit == 0) {
            for ($i = count($r) - 1; $i >= 0; $i--) {
                if ($r[$i]->length() === 0) {
                    unset($r[$i]);
                } else {
                    break;
                }
            }
        }
        return $r;
    }

    /**
     * @static
     * @param StringBuilder $pcrePattern
     * @return Pattern
     */
    static public function compile(StringBuilder $pcrePattern)
    {
        return new self($pcrePattern);
    }
}
