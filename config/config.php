<?php 
use ypum\yauth;
require_once(__DIR__ . "/../mods/all.head.php");
require_once(__DIR__ . "/../mods/config.head.php");
/*
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
*/
# Datenbank-Zugangsdaten
define("DB_NAME", "db_441127_12");
define("DB_USER", "USER441127");

define("DB_HOST", "x96.lima-db.de");
#define("DB_NAME", "db_441127_14");
#define("DB_USER", "USER441127_bsadm");
define("DB_PASS", "BallBierBertha42");
define("TITEL", "LBSV Nds. Mitgliederverwaltung");

# Rechtemanagement (YPUM)
// $berechtigung = $ypum->getUserData();
$uid=0;
if (isset($_SESSION['uid'])) $uid = $_SESSION['uid'];

$anzuzeigendeDaten = array();
$statistik = array();

if($ypum->isBerechtigt(64)){
    $anzuzeigendeDaten[] = array("trenner" => "-");
    require_once(__DIR__ . "/lvl_A_landesverband.php");
}
if($ypum->isBerechtigt(32)){
    $anzuzeigendeDaten[] = array("trenner" => "-");
    require_once(__DIR__ . "/lvl_B_regionalverband.php");
}
if($ypum->isBerechtigt(16)){
    $anzuzeigendeDaten[] = array("trenner" => "-");
    require_once(__DIR__ . "/lvl_C_bsg.php");
}
if($ypum->isBerechtigt(8)){
    $anzuzeigendeDaten[] = array("trenner" => "-");
    require_once(__DIR__ . "/lvl_D_mitglied.php");
}

/*


BSG
---
WHERE FIND_IN_SET(b.id, berechtigte_elemente($uid, 'BSG')) > 0;



*/
/*
DROP FUNCTION IF EXISTS berechtigte_elemente;
DELIMITER //

CREATE FUNCTION berechtigte_elemente(uid INT, target VARCHAR(50))
RETURNS TEXT
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE result TEXT DEFAULT '';
    
    IF target = 'verband' THEN
        SELECT GROUP_CONCAT(DISTINCT verband_id) INTO result
        FROM (
            SELECT v.id as verband_id, r.Nutzer
            FROM b_regionalverband as v
            JOIN b_regionalverband_rechte as r on r.Verband = v.id 
            WHERE r.Nutzer = uid
        ) berechtigungen;
    END IF;
    
    IF target = 'bsg' THEN
        SELECT GROUP_CONCAT(DISTINCT bsg_id) INTO result
        FROM (
            SELECT b.id as bsg_id, b.BSG, Nutzer
            FROM b_bsg as b
            LEFT JOIN b_bsg_rechte as br ON b.id = br.BSG
            JOIN y_user as y ON Nutzer = y.id
            WHERE y.id = uid
            UNION
            SELECT b.id as bsg_id, b.BSG, Nutzer
            FROM b_bsg as b
            LEFT JOIN b_regionalverband_rechte as vr ON b.Verband = vr.Verband
            WHERE Nutzer = uid
        ) berechtigungen;
    END IF;
    
    IF target = 'sparte' THEN
        SELECT GROUP_CONCAT(DISTINCT sparte_id) INTO result
        FROM (
            select s.id as sparte_id, s.Sparte, s.Verband
            from b_sparte as s
            join b_regionalverband_rechte r on s.Verband = r.Verband
            where r.Nutzer=uid
        ) berechtigungen;
    END IF;
    
    RETURN COALESCE(result, '');
END //

DELIMITER ;
*/
?>