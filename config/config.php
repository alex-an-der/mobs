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


require_once(__DIR__."/db_connect.php");

define("TITEL", "LBSV Nds. Mitgliederverwaltung");
# Wie sollen NULL-Werte (=keine Zuordnung) dargestellt werden?
define("NULL_WERT", "---");

# Rechtemanagement (YPUM)
// $berechtigung = $ypum->getUserData();
if (isset($_SESSION['uid']))
{
    $uid = $_SESSION['uid'];
}else{
    die();
}


$anzuzeigendeDaten = array();
$statistik = array();

$bericht = "→ Bericht:";

########################################################################################################
#                                                                                                      #
#                                                                                                      #
## BERECHTIGUNG 10 #####################################################################################

if($ypum->isBerechtigt(1)){
    $anzuzeigendeDaten[] = array("trenner" => "-");
    require_once(__DIR__ . "/lvl_10_admin.php");
}

########################################################################################################
#                                                                                                      #
#                                                                                                      #
## BERECHTIGUNG 20 #####################################################################################

if($ypum->isBerechtigt(64)){
    $anzuzeigendeDaten[] = array("trenner" => "-");
    require_once(__DIR__ . "/lvl_20_landesverband.php");
}

########################################################################################################
#                                                                                                      #
#                                                                                                      #
## BERECHTIGUNG 30 #####################################################################################

if($ypum->isBerechtigt(2)){
    $anzuzeigendeDaten[] = array("trenner" => "-");
    require_once(__DIR__ . "/lvl_30_regional_admin.php");
}

########################################################################################################
#                                                                                                      #
#                                                                                                      #
## BERECHTIGUNG 40 #####################################################################################

$countRechteQ = $db->query("SELECT count(*) as count FROM b_regionalverband_rechte WHERE Nutzer = $uid");
if(isset($countRechteQ['data'][0]['count'])) $countRechte = $countRechteQ['data'][0]['count'];
else                                         $countRechte = 0;  

if($countRechte > 0){
    $anzuzeigendeDaten[] = array("trenner" => "-");
    require_once(__DIR__ . "/lvl_40_regionalverband.php");
}

########################################################################################################
#                                                                                                      #
#                                                                                                      #
## BERECHTIGUNG 50 #####################################################################################

$countRechteQ = $db->query("SELECT count(*) as count FROM b_bsg_rechte WHERE Nutzer = $uid");
if(isset($countRechteQ['data'][0]['count'])) $countRechte = $countRechteQ['data'][0]['count'];
else                                         $countRechte = 0;  

if($countRechte > 0){
    $anzuzeigendeDaten[] = array("trenner" => "-");
    require_once(__DIR__ . "/lvl_50_bsg.php");
    $anzuzeigendeDaten[] = array("trenner" => "-");
    require_once(__DIR__ . "/lvl_51_bsg_import.php");
}

########################################################################################################
#                                                                                                      #
#                                                                                                      #
## BERECHTIGUNG 60 #####################################################################################

if($ypum->isBerechtigt(8)){
    $anzuzeigendeDaten[] = array("trenner" => "-");
    require_once(__DIR__ . "/lvl_60_mitglied.php");
}

/*

WHERE FIND_IN_SET(b.id, berechtigte_elemente($uid, 'BSG')) > 0

https://friendlycaptcha.com/signup/
*/
?>
