<?php

require 'FormHelpers.class.php';

$fh = new FormHelpers;
echo "<form action='' method='post'>";
echo 'Choose a Fred ' . $fh->select_element(array(
    'name' => 'Fred'
    ,'values' => array(1, 2)
    ,'labels' => array('Fred Thomas','Fred Parker')
    ,'selectFrom' => '_POST'
    ,'multiple' => true
    ,'size' => 2
));
echo "<br>Name <input type='text' name='Name' value=\"" . 
    $fh->pfield('Name') . "\"'><br>";
echo '<textarea name="Message">' . $fh->pfield('Message', '(Your message here)') . '</textarea>';
echo "<input type='submit'>";
