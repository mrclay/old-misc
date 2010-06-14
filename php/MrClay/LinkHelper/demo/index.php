<?php

require dirname(__FILE__) . '/../../LinkHelper.php';
require dirname(__FILE__) . '/../OpenAnchor.php';
require dirname(__FILE__) . '/../LinkOrWrapper.php';
require dirname(__FILE__) . '/../ListItem.php';

// just for demo
function ob_showHtml($html) {
    return $html . "<pre>" . htmlspecialchars($html, ENT_QUOTES, 'UTF-8') . "</pre>";
}
$htmlRoot = dirname($_SERVER['SCRIPT_NAME']);
?>
<!doctype html><title>MrClay_LinkHelper demo</title>
<style>
a.current {font-weight: bold;}
li.current {border: 1px solid #ddd;}
</style>
<body>
<h2>MrClay_LinkHelper_ListItem</h2>
<?php
ob_start('ob_showHtml');
$li = new MrClay_LinkHelper_ListItem();
?>
<ul>
    <?= $li->render("$htmlRoot/", "Page 1") ?>

    <?= $li->render("$htmlRoot/?p2", "Page 2") ?>

    <?= $li->render("$htmlRoot/?p3", "Page 3", array(), array('class' => 'item')) ?>

</ul>
<?php ob_end_flush(); ?>


<h2>MrClay_LinkHelper_LinkOrWrapper</h2>
<?php
ob_start('ob_showHtml');
$element = new MrClay_LinkHelper_LinkOrWrapper();
?>
<p>
    <?= $element->render("$htmlRoot/", "Page 1") ?> |
    <?= $element->render("$htmlRoot/?p2", "Page 2") ?> |
    <?= $element->render("$htmlRoot/?p3", "Page 3") ?>

</p>
<?php ob_end_flush(); ?>


<h2>MrClay_LinkHelper_OpenAnchor</h2>
<?php
ob_start('ob_showHtml');
$openA = new MrClay_LinkHelper_OpenAnchor();
?>
<ul>
    <li><?= $openA->render("$htmlRoot/") ?>Page 1</a></li>
    <li><?= $openA->render("$htmlRoot/?p2") ?>Page 2</a></li>
    <li><?= $openA->render("$htmlRoot/?p3", array('class' => 'myClass')) ?>Page 3</a></li>
</ul>
<?php ob_end_flush(); ?>


<h2>Using a wrapper function</h2>
<?php
ob_start('ob_showHtml');
$helper = new MrClay_LinkHelper_ListItem();
function navLi($path, $content) {
    global $helper, $htmlRoot;
    return $helper->render($htmlRoot . $path, $content, array(), array('class' => 'navLink'));
}
?>
<ul>
    <?= navLi("/", "Page 1") ?>

    <?= navLi("/?p2", "Page 2") ?>

    <?= navLi("/?p3", "Page 3") ?>

</ul>
