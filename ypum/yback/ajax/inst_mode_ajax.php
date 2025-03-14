<?php
session_start();
$data = json_decode(file_get_contents('php://input'), true);
require_once(__DIR__."/../include/classes/configmanager.php");
$conf = new ypum\configmanager();
if(!empty($data)){
    if($conf->isRightKey( $_SESSION['ypum_sourcekeyinst'], $data['sourceHash'])) $conf->save("lock", $data, false);
}
?>
