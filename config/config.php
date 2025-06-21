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

# Wie soll dargestellt werden...

# ...wenn etwas ausgewählt werden muss
define("PLEASE_CHOOSE", "Bitte auswählen...");

# ...wenn es keinen Wert im Feld gibt (=NULL)?
define("NULL_WERT", "---");

# ...wenn eine Pflicht-Auswahl keinen Inhalt hat?
define("NULL_BUT_NOT_NULLABLE", "Diese Liste ist noch leer. Bitte zunächst Auswahlmöglichkeiten eintragen.");

# ...wenn die Datenbank einen Fehler zurückgibt
# #FEHLERID# wird bei der Anzeige mit der Fehler-ID (ID im Error-Log) ersetzt.
define("DB_ERROR", "Die Datenbank kann die Daten so nicht speichern. <b>Ist alles korrekt eingegeben?</b><br><br>Wenn du keine Lösung findest, kannst du dich mit der Fehler-ID <b>#FEHLERID#</b> gerne an <a href='mailto:support@mobs24.de?subject=mobs24%20-%20Ich%20erhalte%20die%20Fehler-ID%20#FEHLERID#'>support@mobs24.de</a> wenden. Die Bearbeitung kann aber ggf. etwas Zeit in Anspruch nehmen. ");

# ...wenn es zu einem Serverfehler (kann auch fehlerhafter code sein) kommt
define("SRV_ERROR", "Es kam zu einen allgemeinen Fehler. <b>Ist alles korrekt eingegeben?</b><br><br>Wenn du keine Lösung findest, kannst du dich mit der Fehler-ID <b>#FEHLERID#</b> gerne an <a href='mailto:support@mobs24.de?subject=mobs24%20-%20Ich%20erhalte%20die%20Fehler-ID%20#FEHLERID#'>support@mobs24.de</a> wenden. Die Bearbeitung kann aber ggf. etwas Zeit in Anspruch nehmen.");



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

$countRechteQ = $db->query("SELECT count(*) as count, MAX(bool) AS bErweiterteRechte
                            FROM b_regionalverband_rechte as r
                            JOIN b___an_aus as aa on aa.id = r.erweiterte_rechte 
                            WHERE Nutzer = $uid");

// Gibt es überhaupt eine RV-Berechtigung?
if(isset($countRechteQ['data'][0]['count'])){

    if($countRechteQ['data'][0]['count'] > 0){

        $countRechte = $countRechteQ['data'][0]['count'];
        $erweiterteRechte = $countRechteQ['data'][0]['bErweiterteRechte'];

        // Zusätzlich eine erweiterte Berechtigung?
        if($erweiterteRechte){
            $anzuzeigendeDaten[] = array("trenner" => "-");
            require_once(__DIR__ . "/lvl_30_regional_admin.php");
        }

        ########################################################################################################
        #                                                                                                      #
        #                                                                                                      #
        ## BERECHTIGUNG 40 #####################################################################################

        $anzuzeigendeDaten[] = array("trenner" => "-");
        require_once(__DIR__ . "/lvl_40_regionalverband.php");
    }
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
