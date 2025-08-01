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
/*$mitgliederauswahl =        "SELECT m.id AS id, CONCAT(Vorname, ' ', Nachname, ' (',COALESCE(b.BSG, '---'),', ', m.id,' )') as anzeige
                            FROM b_mitglieder AS m
                            LEFT JOIN b_bsg AS b ON b.id=m.BSG
                            WHERE FIND_IN_SET(m.id, berechtigte_elemente($uid, 'mitglied')) > 0 OR
                            FIND_IN_SET(m.id, berechtigte_elemente($uid, 'individuelle_mitglieder')) > 0
                            ORDER BY anzeige;";*/


// Wird auch an anderer Stelle verwendet! (z.B. Rechte für Regionalverband)
$mitgliederconcat = "CONCAT(Vorname, ' ', Nachname, ' (',COALESCE(b.BSG, '---'),', ', m.id,')')";

// JEDER, der irgendwie ausgewählt wird, muss sich einloggen können und daher in einer BSG sein.
$mitgliederauswahl = "SELECT m.id AS id, $mitgliederconcat as anzeige
                            FROM b_mitglieder AS m
                            LEFT JOIN b_bsg AS b ON b.id=m.BSG
                            WHERE (FIND_IN_SET(m.id, berechtigte_elemente($uid, 'mitglied')) > 0 OR
                            FIND_IN_SET(m.id, berechtigte_elemente($uid, 'individuelle_mitglieder')) > 0) AND
                            m.BSG IS NOT NULL
                            ORDER BY anzeige;";

$salden = array(
    "tabellenname" => "b_meldeliste",
    "auswahltext" => "$bericht Salden",
    "writeaccess" => false,
    "import" => false,
    "query" => "SELECT 
                MAX(a.BSG) as id, -- Pflicht-id, hier nicht relevant
                b.BSG as BSG,
                a.Abrechnungsjahr,
                SUM(a.HABEN) as HABEN,
                SUM(a.SOLL) as SOLL,
                (SUM(a.HABEN) - SUM(a.SOLL)) AS Saldo,
                r.Verband as Empfaenger

                FROM (
                SELECT BSG_ID as BSG, Beitragsjahr as Abrechnungsjahr, Betrag as SOLL, 0 as HABEN, Beitragsstelle as Empfaenger
                FROM b_meldeliste

                UNION ALL

                SELECT BSG, Abrechnungsjahr, 0 as SOLL, Haben as HABEN, Empfaenger
                FROM b_zahlungseingaenge
                ) as a
                JOIN b_bsg as b on b.id = a.BSG
                JOIN b_regionalverband as r on a.Empfaenger = r.id

                WHERE 
                FIND_IN_SET(b.id, berechtigte_elemente($uid, 'bsg')) > 0 OR
                FIND_IN_SET(b.Verband, berechtigte_elemente($uid, 'verband_erweitert')) > 0 
                GROUP BY BSG, Abrechnungsjahr, r.Verband;

                    ",
                    "spaltenbreiten" => array(
                        "BSG"             => "300",
                        "Abrechnungsjahr" => "120",
                        "Soll"            => "100",
                        "Haben"           => "100",
                        "Saldo"           => "100",
                        "Empfaenger"      => "300"
                    )
                );

$rechteuebersicht = array(
    "tabellenname" => "b_regionalverband_rechte",
    "auswahltext" => "$bericht Rechte-Übersicht",
    "writeaccess" => false,
    "query" => "SELECT 1 as id, $mitgliederconcat as Mitglied, Typ, Berechtigung FROM
                    (SELECT Nutzer, 'Verband' as Typ, v.Verband as Berechtigung
                    FROM b_regionalverband_rechte as r 
                    JOIN b_regionalverband as v on v.id=r.Verband

                    UNION ALL

                    SELECT Nutzer, 'BSG' as Typ, CONCAT(bb.BSG, ' (', v.Verband,')') as Berechtigung
                    FROM b_bsg_rechte as r 
                    JOIN b_bsg as bb on bb.id=r.BSG
                    JOIN b_regionalverband as v on v.id=bb.Verband) as rechte
                    JOIN b_mitglieder as m on m.y_id = rechte.Nutzer
                    JOIN b_bsg as b on b.id = m.bsg    
                    
                ",
    "spaltenbreiten" => array(
        "Verband"                       => "300",
        "Nutzer"                        => "300",
        "erweiterte_Rechte"             => "100"
    )
);

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
