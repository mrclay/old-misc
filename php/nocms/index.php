<?php

set_include_path(
    dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib'
	. PATH_SEPARATOR . get_include_path() 
);

// autoload
function nocms_autoload($class) {
    require_once str_replace('_', '/', $class) . '.php';
}
spl_autoload_register('nocms_autoload');

// template
function h($txt) {
    return htmlspecialchars($txt, ENT_QUOTES);
}

$config = (require './config.php');
$nocms = new NoCms($config);
$nocms->init();

