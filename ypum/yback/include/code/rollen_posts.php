
<?php
if(isset($_POST['MACH_EDIT'])){

	$args[] = $_POST['r_bit'];
	$args[] = $_POST['r_name'];
	$args[] = $_POST['r_role_comment'];

	if(isset($_POST['r_role_active'])){
		if($_POST['r_role_active']=="on") $args[] = true;
		else $args[] = false;
	}else{
		$args[] = false;
	}

	$dbm->query("REPLACE INTO y_roles (bit, name, role_comment, role_active) values(?,?,?,?)",$args, false);
}
?>