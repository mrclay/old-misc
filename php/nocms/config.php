<?php

$config = array(
    'password' => ''
    ,'passwordIsMd5' => false
    
    ,'htmlRoot' => dirname($_SERVER['SCRIPT_NAME'])
    ,'siteHome' => dirname(dirname($_SERVER['SCRIPT_NAME'])) . '/'
    
    ,'contentPath' => dirname(__FILE__) . '/content'
);

return $config;
