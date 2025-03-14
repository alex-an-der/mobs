<?php
session_start();
$data = json_decode(file_get_contents('php://input'), true);
require_once(__DIR__."/../include/classes/configmanager.php");
require_once(__DIR__."/../include/classes/databasemanager.php");
$conf = new ypum\configmanager();

if(!empty($data)){
	if($conf->isRightKey( $_SESSION['ypum_sourcekeyscan'], $data['sourceHash'])){
		
		$secDir = $conf->getSecDir();
		$dbm = new ypum\databasemanager($secDir);

		$values = array();
		$values[] = $data['data'];
		$values[] = 1;

		if (!empty($data['data'])) {
			$row=$dbm->query("REPLACE INTO y_sites (dir, roles) VALUES (?,?)", $values, false);
		} else {
			die;
		}
	}
}
?>