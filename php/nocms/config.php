<?php

$config = array(
    'password' => '',
    'passwordIsMd5' => true, // http://pajhome.org.uk/crypt/md5/
    
    // # of previous versions to store
    'numBackups' => 2,
    
    // your user's site e.g. where the editable content appears
    'siteName' => $_SERVER['SERVER_NAME'],
    'siteHome' => str_replace('//', '/', dirname(dirname($_SERVER['SCRIPT_NAME'])) . '/'),
    
    // ends with "../nocms"
    'htmlRoot' => dirname($_SERVER['SCRIPT_NAME']),
    
    // where editable content lives
    'contentPath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'content',
    
    // removes <?  // <% 
    'stripCode' => true, // not implemented
);

return $config;
