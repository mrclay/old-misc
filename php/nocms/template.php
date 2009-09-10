<?php 
header('Content-type: text/html;charset=utf-8'); 
?><!doctype html><html><head>
<title>NoCms : <?= h($title) ?></title>
</head>
<body>
<p><a href='<?= h($home) ?>'>Site Home</a> 
<?php if ($loggedIn): ?>
 | <a href='<?= h($actionRoot . '/') ?>'>List Content</a>
 | <a href='<?= h($actionRoot . '/logout') ?>'>Logout</a>
<?php endif; ?>
</p>
<hr>
 
<?= $content ?>

<hr>
<p><small>NoCms 0.0</small></p>
<script src='http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js'></script>
<?= $beforeBodyEnd ?>
</body></html>