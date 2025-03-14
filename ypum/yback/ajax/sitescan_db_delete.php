<?php
session_start();
$data = json_decode(file_get_contents('php://input'), true);
require_once(__DIR__."/../include/classes/configmanager.php");
$conf = new ypum\configmanager();

if(!empty($data)){
	if($conf->isRightKey( $_SESSION['ypum_sourcekeyscan'], $data['sourceHash'])){

		require_once(__DIR__."/../include/classes/databasemanager.php");
		$conf = new ypum\configmanager();
		$secDir = $conf->getSecDir();
		$dbm = new ypum\databasemanager($secDir);

		$values = array();
		$values[] = $data['id'];

		$dbm->query("delete from y_sites where id=?",$values,false);

	}
}
?>