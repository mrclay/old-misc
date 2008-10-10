<?php

require '../CookieStorage.php';

header('Cache-Control: private, no-cache');

$name = 'cookieStorageTest';
$userinfo = 'id:62572,email:bob@yahoo.com,name:Bob';

$storage = new MrClay_CookieStorage(array(
    'secret' => '67676kmcuiekihbfyhbtfitfytrdo=op-p-=[hH8'
));

if (isset($_GET['do'])) {
    switch ($_GET['do']) {
    case 'update':
        $storage->store($name, $userinfo);
        break;
    case 'tamper':
        $evilStorage = new MrClay_CookieStorage(array(
            'secret' => 'DifferentSecret'
        ));
        $evilStorage->store($name, 'id:345,email:phil@yahoo.com,name:Phil');
        break;
    case 'shortCookie':
        $storage->setOption('expire', time() + 10);
        $storage->store($name, $userinfo);
        break;
    }
    header('Location: test.php');
    exit();
}

$user = $storage->fetch($name);
if (null === $user) {
    $storage->store($name, $userinfo);
    echo "No cookie found. User info stored!";
} elseif (false === $user) {
    // tampering
    $storage->store($name, $userinfo);
    echo htmlspecialchars($_COOKIE[$name]) . '<br>';
    echo "hack attempt foiled! Cookie re-saved.";
} else {
    // good data
?>
    <p>User data: <?php echo $user; ?></p>
    <p>Cookie stored: <?php echo date('r', $storage->getTimestamp($name)); ?> 
    <ul>
        <li><a href="?do=update">Re-save cookie</a>
        <li><a href="?do=shortCookie">Set short-lived cookie</a>
        <li><a href="?do=tamper">Tamper with cookie</a>
    </ul>
<?php
}
