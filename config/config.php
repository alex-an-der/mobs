<?php 
# Datenbank-Zugangsdaten
define("DB_NAME", "db_441127_12");
define("DB_HOST", "x96.lima-db.de");
define("DB_USER", "USER441127");
define("DB_PASS", "BallBierBertha42");

# Anzeige bei Spalten, die über Fremdschlüssel verknüpft sind
define("spartenleitung1_funktionaer","##Vorname## ##Nachname## (##eMail##)");
define("spartenleitung2_funktionaer","##Nachname##, ##Vorname##");
define("mitglieder_geschlecht", "##geschlecht##");
define("mitglieder_unternehmen", "##Name##");
define("spartenmitglieder_StammSparte", "select id, Spartenname as anzeige from sparten order by anzeige;");
define("spartenmitglieder_StammMitglieder", "select m.id as id, CONCAT(m.Nachname,', ',m.Vorname, ' (',u.Name,')') as anzeige from mitglieder as m left join unternehmen as u on m.Unternehmen=u.id;");

$anzeigeSubstitutionen = array();
$anzeigeSubstitutionen['sparten_mitglieder']['Sparte'] = "select id, Spartenname as anzeige from sparten order by anzeige;";
$anzeigeSubstitutionen['sparten_mitglieder']['Mitglied'] = "select m.id as id, CONCAT(m.Nachname,', ',m.Vorname, ' (',u.Name,')') as anzeige from mitglieder as m left join unternehmen as u on m.Unternehmen=u.id order by m.Nachname;";

# Welche Tabellen sollen nicht angezeigt werden?
define("NOSHOWS", ["yuser", "geschlechter"]);
# define("NOSHOWS", ["log", "yuser", "geschlechter"]);

?>