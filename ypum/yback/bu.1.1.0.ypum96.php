<?php 
namespace ypum;
@session_start(); 
$lastURI = $_SERVER["SCRIPT_URI"];

if(isset($_POST['seccheck'])){
    echo 254965; 
    die();
}

require_once(__DIR__."/include/inc_main_96.php");

// Installationsmodus? Überprüfung überspringen und Warnung ausgeben
$im =  $conf->isInstallmodus();
if($im){
    $im_warning = "";
    $im_warning .= "<div class='alert alert-danger' role='alert'>";
    $im_warning .= "<b>ACHTUNG!</b> YPUM befindet sich im Konfigurationsmodus und ist <b>nicht</b> aktiv.";
    $im_warning .= "</div>";   
    echo $im_warning;
}

// Muss die SessID erneuert werden?
$configdaten = $conf->load("divers");
$seksTOrenew=$configdaten['sessionrenew'] * 60;
$seksTOdiscard=$configdaten['sessiondiscard'] * 60;
if (empty($_SESSION['ID_created']) || ($_SESSION['ID_created'] + $seksTOrenew) < time()) {
    session_regenerate_id();
    $_SESSION['ID_created'] = time();
}

// Session zerstören, wenn nötig
if (!$im && (empty($_SESSION['SESS_created']) || ($_SESSION['SESS_created'] + $seksTOdiscard) < time())) {
    session_unset();
    session_destroy();
    $_SESSION = array();
    $pathToLogin = $conf->getPathToLogin();
    $_SESSION['logout_error'] = "Sie wurden wegen zu langer Inaktivit&auml;t abgemeldet";
    @header("Location: $pathToLogin");
    $conf->redirect($pathToLogin,$lastURI);
}else{
    $_SESSION['SESS_created'] = time();
}
require_once(realpath(__DIR__."/include/classes/yauth.php"));
$ypum = new yauth($dbm, $conf, $configdaten, $im);


?>
