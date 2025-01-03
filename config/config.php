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

$anzuzeigendeDaten = array();
# tabellenname => Nur hierein kann in dieser Ansicht ein insert oder update ausgeführt werden.
#              => Basistabelle für Referenzierung in anderen Tabellen
# query        => Es muss eine Spalte mit dem Namen "id" angefordert werden, die als eindeutiger Schlüssel verwendet wird.
#              => Die Spalte "id" wird nicht angezeigt. 
#              => Soll die ID des Datensatzes angezeigt werden, muss diese ein zweites Mal angefordert werden (z.B. SELECT id, id as LfdNr. from ...)
$anzuzeigendeDaten[0] = array(
    "tabellenname" => "sparten",
    "auswahltext" => "0-Die BSV-Sparten",
    "query" => "select id, Spartenname as Sparte, beitrag as `Beitrag (€)`, spartenleitung as Leitung, spartenleitungvertretung as Vertreter from sparten order by Sparte;"
);
$anzuzeigendeDaten[1] = array(
    "tabellenname" => "sparten_mitglieder",
    "auswahltext" => "1-Sparten-Mitglieder",
    "query" => "select id, Sparte, Mitglied from sparten_mitglieder;"
);

$anzeigeSubstitutionen = array();
$anzeigeSubstitutionen['sparten_mitglieder']['Sparte'] = "select id, Spartenname as anzeige from sparten order by anzeige;";
$anzeigeSubstitutionen['sparten_mitglieder']['Mitglied'] = "select m.id as id, CONCAT(m.Nachname,', ',m.Vorname, ' (',u.Name,')') as anzeige from mitglieder as m left join unternehmen as u on m.Unternehmen=u.id order by m.Nachname;";

# Welche Tabellen sollen nicht angezeigt werden?
define("NOSHOWS", ["yuser", "geschlechter"]);
# define("NOSHOWS", ["log", "yuser", "geschlechter"]);

?>