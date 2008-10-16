<?php

require '../CookieStorage.php';

header('Cache-Control: private, no-cache');

$name = 'cookieStorageTest2';
$userinfo = 'id:62572,email:bob@yahoo.com,name:Bob';

$storage = new MrClay_CookieStorage(array(
    'secret' => '67676kmcuiekihbfyhbtfitfytrdo=op-p-=[hH8'
    ,'path' => '' // this URL only for testing
    ,'mode' => MrClay_CookieStorage::MODE_ENCRYPT
));

if (isset($_GET['do'])) {
    switch ($_GET['do']) {
    case 'update':
        $storage->store($name, $userinfo);
        break;
    case 'tamper':
        $evilStorage = new MrClay_CookieStorage(array(
            'secret' => 'DifferentSecret'
            ,'path' => '' // this URL only for testing
            ,'mode' => MrClay_CookieStorage::MODE_ENCRYPT
        ));
        $evilStorage->store($name, 'id:345,email:phil@yahoo.com,name:Phil');
        break;
    case 'shortCookie':
        $storage->setOption('expire', time() + 10);
        $storage->store($name, $userinfo);
        break;
    }
    header('Location: test2.php');
    exit();
}

$user = $storage->fetch($name);
if (null === $user) {
    $storage->store($name, $userinfo);
    echo "No cookie found. User info stored!";
} elseif (false === $user) {
    // tampering
    $storage->store($name, $userinfo);
    echo "hack attempt foiled! Cookie re-saved.";
} else {
    // good data
?>
    <p>User data: <?php echo $user; ?></p>
    <p>Cookie stored: <?php echo date('r', $storage->getTimestamp($name)); ?> 
    <ul>
        <li><a href="?do=update">Re-save cookie</a>
        <li><a href="?do=shortCookie">Set short-lived cookie (reload after 10 seconds)</a>
        <li><a href="?do=tamper">Tamper with cookie</a>
    </ul>
    <p>$_COOKIE['<?php echo $name; ?>'] == <code><?php echo htmlspecialchars($_COOKIE[$name]); ?></code></p>
    <hr>
    <p><a href="http://code.google.com/p/mrclay/source/browse/trunk/php/MrClay/CookieStorage/test2.php">view PHP source</a></p>
<?php
}
