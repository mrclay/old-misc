<?php 
header('Content-type: text/html;charset=utf-8'); 
?><!doctype html><html><head>
<title>NoCms : <?= h($title) ?></title>
</head>
<body>
<p><a href='<?= h($siteHome) ?>'><?= h($siteName) ?></a> 
<?php if ($loggedIn): ?>
 | <a href='<?= h($actionRoot . '/') ?>'>List Content</a>
 | <a href='<?= h($actionRoot . '/logout') ?>'>Logout</a>
<?php endif; ?>
</p>
<hr>
 
<?= $content ?>

<hr>
<p><small>Powered by <a href="http://code.google.com/p/mrclay/source/browse/trunk/php/nocms/README.txt">NoCms</a> 
    <?php readfile('VERSION.txt'); ?>
    by <a href="http://mrclay.org/">Steve Clay</a></small>
</p>
<script src='http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js'></script>
<?= $beforeBodyEnd ?>
</body></html>