<?php 
use ypum\yauth;
require_once(__DIR__ . "/../user_includes/all.head.php");
require_once(__DIR__ . "/../user_includes/config.head.php");

# ------------------------------------------------------------------------------------------------
# Stage-Konfiguration
# ------------------------------------------------------------------------------------------------

$DEBUG = 1;

# ------------------------------------------------------------------------------------------------


if($DEBUG){
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
}

require_once("db_connect.php");


# Rechtemanagement (YPUM)
// $berechtigung = $ypum->getUserData();
$uid=0;
if (isset($_SESSION['uid'])) $uid = $_SESSION['uid'];

$anzuzeigendeDaten = array();
$statistik = array();

if($ypum->isBerechtigt(1)){
    $anzuzeigendeDaten[] = array("trenner" => "-");
    require_once(__DIR__ . "/lvl_X_admin.php");
}

// Auf der oberste Ebene muss ich über YPUM berechtigen
if($ypum->isBerechtigt(64)){
    $anzuzeigendeDaten[] = array("trenner" => "-");
    require_once(__DIR__ . "/lvl_A_landesverband.php");
}

// Anzeige nach Berechtigungen - habe ich eine, sehe ich was
$countRechteQ = $db->query("SELECT count(*) as count FROM b_regionalverband_rechte WHERE Nutzer = $uid");
if(isset($countRechteQ['data'][0]['count'])) $countRechte = $countRechteQ['data'][0]['count'];
else                                         $countRechte = 0;  

if($countRechte > 0){
    $anzuzeigendeDaten[] = array("trenner" => "-");
    require_once(__DIR__ . "/lvl_B_regionalverband.php");
}

// Anzeige nach Berechtigungen - habe ich eine, sehe ich was
$countRechteQ = $db->query("SELECT count(*) as count FROM b_bsg_rechte WHERE Nutzer = $uid");
if(isset($countRechteQ['data'][0]['count'])) $countRechte = $countRechteQ['data'][0]['count'];
else                                         $countRechte = 0;  

if($countRechte > 0){
    $anzuzeigendeDaten[] = array("trenner" => "-");
    require_once(__DIR__ . "/lvl_C_bsg.php");
}
if($ypum->isBerechtigt(8)){
    $anzuzeigendeDaten[] = array("trenner" => "-");
    require_once(__DIR__ . "/lvl_D_mitglied.php");
}

/*

WHERE FIND_IN_SET(b.id, berechtigte_elemente($uid, 'BSG')) > 0

https://friendlycaptcha.com/signup/
*/
?>
