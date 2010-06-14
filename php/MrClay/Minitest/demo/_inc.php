<?php

require_once dirname(__FILE__) . '/../../Minitest.php';

if (isset($_GET['text'])) {
    MrClay_Minitest::defaultRenderer(new MrClay_Minitest_CaseRenderer_Text());
} elseif (isset($_GET['allPassed'])) {
    MrClay_Minitest::defaultRenderer(new MrClay_Minitest_CaseRenderer_AllPassed());
}

function nav() {
    if (get_class(MrClay_Minitest::defaultRenderer()) != 'MrClay_Minitest_CaseRenderer_Html') {
        return;
    }
    ?>
<p><a href="?text">Text renderer</a> | <a href="?allPassed">AllPassed renderer</a></p>
    <?php
}