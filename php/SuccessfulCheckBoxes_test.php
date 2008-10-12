<?php

require_once 'SuccessfulCheckBoxes.class.php';

// edits POST array
$SCB = new SuccessfulCheckBoxes(array('values01' => true));

if (!isset($_POST['status1'])) {
	$_POST['status1'] = false;
	$_POST['status2'] = false;
	$_POST['status3'] = false;
}

?><form action="SuccessfulCheckBoxes_test.php" method="post">
<ul>
	<li><?php echo $SCB->check('status1', $_POST['status1']); ?> Status 1
	<li><?php echo $SCB->check('status2', $_POST['status2']); ?> Status 2
	<li><?php echo $SCB->check('status3', $_POST['status3']); ?> Status 3
</ul>
<input type="submit" value="submit">
</div>
</form>
<?php

if (isset($_POST['status1'])) {
    echo "<pre>\$_POST == " . htmlspecialchars(var_export($_POST, 1)) . "</pre>";
}

?>