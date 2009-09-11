<?php

$config = array(
    'password' => ''
    ,'passwordIsMd5' => false
    
    // removes <?  // <% 
    ,'stripCode' => true
    
    // your user's site e.g. where this content appears
    ,'siteHome' => dirname(dirname($_SERVER['SCRIPT_NAME'])) . '/'
    
    // ends with "../nocms"
    ,'htmlRoot' => dirname($_SERVER['SCRIPT_NAME'])
    ,'contentPath' => dirname(__FILE__) . '/content'
);

return $config;
