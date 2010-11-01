<?php

class MrClay_QAD_Layout_Default {
    public static function layout(Zend_View $view) {
        ?>
<!doctype html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <?php echo $view->headTitle() ?>
    <?php echo $view->headScript() ?>
    <?php echo $view->headStyle() ?>
</head>
<body>
    <div id="nav"><?php echo $view->placeholder('nav') ?></div>
    <div id="content"><?php echo $view->layout()->content ?></div>
</body><?php
    }
}