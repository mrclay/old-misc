<?php

require_once './_inc.php';

class Test_QuiteObviousThings extends MrClay_Minitest_Case {
    function test_numbers() {
        $this->assertTrue(1 == 1);
        $this->assertTrue(1 == time()%2, 'Intermittent bug (<a href="">reload</a>)!');
        $this->assertIdentical(1, 1, 'numbers are identical iff types match');
    }

    function test_strings() {
        $this->assertPattern('@Hello@', 'Hello World!');
    }

    function before() {
        // set up stuff here
    }
}

MrClay_Minitest::register(new Test_QuiteObviousThings);

nav();
