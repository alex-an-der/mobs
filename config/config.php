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


WHERE FIND_IN_SET(b.id, berechtigte_elemente($uid, 'BSG')) > 0

*/

/*
DROP TABLE IF EXISTS `rollback`;
CREATE TABLE `rollback` ( 
  `ID` BIGINT AUTO_INCREMENT NOT NULL,
  `zeit` DATETIME NULL DEFAULT CURRENT_TIMESTAMP ,
  `autor` VARCHAR(200) NULL,
  `eintrag` VARCHAR(1000) NULL,
   PRIMARY KEY (`ID`)
)
ENGINE = InnoDB;


DROP VIEW IF EXISTS v_mitglieder_in_bsg_gesamt;
CREATE VIEW v_mitglieder_in_bsg_gesamt as
SELECT z.Mitglied as mitglied , z.BSG as BSG
FROM b_zusaetzliche_bsg_mitgliedschaften as z
union
SELECT m.id as mitglied, m.BSG as bsg
FROM b_mitglieder as m
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

    SELECT 
    CASE target
        WHEN 'verband' THEN (
            SELECT GROUP_CONCAT(DISTINCT verband_id)
            FROM (
                SELECT v.id as verband_id, r.Nutzer
                FROM b_regionalverband as v
                JOIN b_regionalverband_rechte as r on r.Verband = v.id 
                WHERE r.Nutzer = uid
            ) berechtigungen
        )
        WHEN 'bsg' THEN (
            SELECT GROUP_CONCAT(DISTINCT bsg_id)
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
            ) berechtigungen
        )
        WHEN 'sparte' THEN (
            SELECT GROUP_CONCAT(DISTINCT sparte_id)
            FROM (
                select s.id as sparte_id, s.Sparte, s.Verband
                from b_sparte as s
                join b_regionalverband_rechte r on s.Verband = r.Verband
                where r.Nutzer=uid
            ) berechtigungen
        )
        WHEN 'mitglied' THEN (
            SELECT GROUP_CONCAT(DISTINCT ID)
            FROM (
                SELECT member_bsg.id as ID, member_bsg.bsg as BSG, v.id as Verband 
                FROM(
                    SELECT z.Mitglied as id , z.BSG as bsg
                    FROM b_zusaetzliche_bsg_mitgliedschaften as z
                    union
                    SELECT m.id as id, m.BSG as bsg
                    FROM b_mitglieder as m
                ) member_bsg
                JOIN b_bsg on b_bsg.id = member_bsg.bsg
                JOIN b_regionalverband as v on v.id = b_bsg.Verband
            ) b_und_v
            WHERE (FIND_IN_SET(b_und_v.BSG, berechtigte_elemente_sub1(uid, 'BSG')) > 0) 
            OR (FIND_IN_SET(b_und_v.Verband, berechtigte_elemente_sub1(uid, 'verband')) > 0)
        )
        WHEN 'stammmitglied' THEN (
            SELECT GROUP_CONCAT(DISTINCT ID)
            FROM (
                SELECT member_bsg.id as ID, member_bsg.bsg as BSG, v.id as Verband 
                FROM(
                    SELECT m.id as id, m.BSG as bsg
                    FROM b_mitglieder as m
                ) member_bsg
                JOIN b_bsg on b_bsg.id = member_bsg.bsg
                JOIN b_regionalverband as v on v.id = b_bsg.Verband
            ) b_und_v
            WHERE (FIND_IN_SET(b_und_v.BSG, berechtigte_elemente_sub1(uid, 'BSG')) > 0) 
            OR (FIND_IN_SET(b_und_v.Verband, berechtigte_elemente_sub1(uid, 'verband')) > 0)
        )
        ELSE ''
    END INTO result;
    
    RETURN COALESCE(result, '');
END //

DELIMITER ;



DROP FUNCTION IF EXISTS berechtigte_elemente_sub1;
DELIMITER //

CREATE FUNCTION berechtigte_elemente_sub1(uid INT, target VARCHAR(50))
RETURNS TEXT
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE result TEXT DEFAULT '';

    SELECT 
    CASE target
        WHEN 'verband' THEN (
            SELECT GROUP_CONCAT(DISTINCT verband_id)
            FROM (
                SELECT v.id as verband_id, r.Nutzer
                FROM b_regionalverband as v
                JOIN b_regionalverband_rechte as r on r.Verband = v.id 
                WHERE r.Nutzer = uid
            ) berechtigungen
        )
        WHEN 'bsg' THEN (
            SELECT GROUP_CONCAT(DISTINCT bsg_id)
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
            ) berechtigungen
        )
        WHEN 'sparte' THEN (
            SELECT GROUP_CONCAT(DISTINCT sparte_id)
            FROM (
                select s.id as sparte_id, s.Sparte, s.Verband
                from b_sparte as s
                join b_regionalverband_rechte r on s.Verband = r.Verband
                where r.Nutzer=uid
            ) berechtigungen
        )
        WHEN 'mitglied' THEN (
            SELECT GROUP_CONCAT(DISTINCT ID)
            FROM (
                SELECT member_bsg.id as ID, member_bsg.bsg as BSG, v.id as Verband 
                FROM(
                    SELECT z.Mitglied as id , z.BSG as bsg
                    FROM b_zusaetzliche_bsg_mitgliedschaften as z
                    union
                    SELECT m.id as id, m.BSG as bsg
                    FROM b_mitglieder as m
                ) member_bsg
                JOIN b_bsg on b_bsg.id = member_bsg.bsg
                JOIN b_regionalverband as v on v.id = b_bsg.Verband
            ) b_und_v
            WHERE (FIND_IN_SET(b_und_v.BSG, berechtigte_elemente_sub1(uid, 'BSG')) > 0) 
            OR (FIND_IN_SET(b_und_v.Verband, berechtigte_elemente_sub1(uid, 'verband')) > 0)
        )
        WHEN 'stammmitglied' THEN (
            SELECT GROUP_CONCAT(DISTINCT ID)
            FROM (
                SELECT member_bsg.id as ID, member_bsg.bsg as BSG, v.id as Verband 
                FROM(
                    SELECT m.id as id, m.BSG as bsg
                    FROM b_mitglieder as m
                ) member_bsg
                JOIN b_bsg on b_bsg.id = member_bsg.bsg
                JOIN b_regionalverband as v on v.id = b_bsg.Verband
            ) b_und_v
            WHERE (FIND_IN_SET(b_und_v.BSG, berechtigte_elemente_sub1(uid, 'BSG')) > 0) 
            OR (FIND_IN_SET(b_und_v.Verband, berechtigte_elemente_sub1(uid, 'verband')) > 0)
        )
        ELSE ''
    END INTO result;
    
    RETURN COALESCE(result, '');
END //

DELIMITER ;

*/
?>