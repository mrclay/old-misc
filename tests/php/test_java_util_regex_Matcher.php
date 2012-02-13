<?php
// @todo convert to PHPUnit


require_once '_inc.php';

require_once 'java/lang/JString.php';
require_once 'java/util/regex/Pattern.php';
require_once 'java/util/regex/Matcher.php';

function test_java_util_regex_Matcher()
{
    //global $thisDir;
   
    $tests = array(
        array(
            'pattern' => '/cat/',
            'numGroups' => 0,
            'input' => 'one cat two cats in the yard',
            'replacement' => 'dog',
        ),
        array(
            'pattern' => '/([0-9a-f])\1([0-9a-f])\2([0-9a-f])\3/',
            'numGroups' => 3,
            'input' => 'Hello World! aa66ff fefe 44ee6677 gg',
            'replacement' => '######',
        ),
        array(
            'pattern' => '/^([0-9a-f])\1([0-9a-f])\2([0-9a-f])\3/',
            'numGroups' => 3,
            'input' => 'Hello World! aa66ff fefe 44ee6677 gg',
            'replacement' => '######',
        ),
        array(
            'pattern' => '/([0-9a-f])\1([0-9a-f])\2([0-9a-f])\3/',
            'numGroups' => 3,
            'input' => 'Hello World! aa66ff fefe 44ee6677 gg',
            'replacement' => '$1$1$2$2$3$3',
        ),
        array(
            'pattern' => '/^([0-9a-f])\1([0-9a-f])\2([0-9a-f])\3/',
            'numGroups' => 3,
            'input' => 'aa66ff',
            'replacement' => '',
        ),
    );
    foreach ($tests as $test) {
        $pattern = new java\util\regex\Pattern($test['pattern'], $test['numGroups']);
        $matcher = $pattern->matcher($test['input']);
        var_export($matcher->matches()); echo "\n";
        while ($matcher->find()) {
            var_export($matcher->group());
        }
        $matcher->reset(); echo "\n";
        $sb = '';
        while ($matcher->find()) {
            $matcher->appendReplacement($sb, $test['replacement']);
        }
        $matcher->appendTail($sb);
        var_export($sb);
        echo "\n\n";
    }
}

test_java_util_regex_Matcher();
