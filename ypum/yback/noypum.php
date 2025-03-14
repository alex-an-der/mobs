<?php 
namespace ypum;
session_start(); 
$_SESSION['lastSite'] = $_SERVER['SCRIPT_FILENAME'];
// Einfache Überprüfung, ob diese Seite eingebunden ist (wir von sitescan.php genutzt)
// Der code wird als "absichtlich nicht eingebunden" erkannt.
if(isset($_POST['seccheck'])){
    echo 159874; 
    die();
}
require_once(__DIR__."/include/inc_main_96.php");
require_once(realpath(__DIR__."/include/classes/yauth.php"));

$configdaten = $conf->load("divers");
$noypum = new yauth($dbm, $conf, $configdaten, false, true);
?>