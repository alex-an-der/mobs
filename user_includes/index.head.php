<?php
require_once(__DIR__ . "/../inc/include.php");
$uid = $_SESSION['uid'];
$res = $db->query("SELECT count(*) as anz FROM b_mitglieder WHERE y_id = ? AND BSG IS NOT NULL;", [$uid]);
$anz = $res['data'][0]['anz'];
if (!$anz) {
    ob_end_clean();
    header("Location: user_code/noBSG.php");
    exit();
}
?>