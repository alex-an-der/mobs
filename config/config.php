<?php 
require_once(__DIR__ . "/../mods/all.head.php");
require_once(__DIR__ . "/../mods/config.head.php");
/*
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
*/
# Datenbank-Zugangsdaten
define("DB_NAME", "db_441127_14");
define("DB_HOST", "x96.lima-db.de");
define("DB_USER", "USER441127_bsadm");
define("DB_PASS", "BallBierBertha42");
define("TITEL", "LBSV Nds. Mitgliederverwaltung");

# Rechtemanagement (YPUM)
$uid=0;
if (isset($_SESSION['uid'])) $uid = $_SESSION['uid'];

$anzuzeigendeDaten = array();
$statistik = array();

require_once(__DIR__ . "/lvl_A_landesverband.php");
$anzuzeigendeDaten[] = array("trenner" => "-");
require_once(__DIR__ . "/lvl_B_regionalverband.php");
$anzuzeigendeDaten[] = array("trenner" => "-");
require_once(__DIR__ . "/lvl_C_bsg.php");
$anzuzeigendeDaten[] = array("trenner" => "-");
require_once(__DIR__ . "/lvl_D_mitglied.php");
?>