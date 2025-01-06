<?php 
require_once(__DIR__ . "/../mods/all.head.php");
require_once(__DIR__ . "/../mods/config.head.php");
# Datenbank-Zugangsdaten
define("DB_NAME", "db_441127_14");
define("DB_HOST", "x96.lima-db.de");
define("DB_USER", "USER441127_bsadm");
define("DB_PASS", "BallBierBertha42");


$anzuzeigendeDaten = array();

# tabellenname => Nur hierein kann in dieser Ansicht ein insert oder update ausgeführt werden.
#              => Basistabelle für Referenzierung in anderen Tabellen
# query        => Es muss eine Spalte mit dem Namen "id" angefordert werden, die als eindeutiger Schlüssel verwendet wird.
#              => Die Spalte "id" wird nicht angezeigt. 
#              => Soll die ID des Datensatzes angezeigt werden, muss diese ein zweites Mal angefordert werden (z.B. SELECT id, id as LfdNr. from ...)
#              => Es können nur Spalten bearbeitet werden, die nicht mit einem Alias angefordert werden. Beispiel: SELECT Nachname, vName as Vorname -> nur Nachname kann bearbeitet werden.
#
# KOMPLETTBEISPIEL:
# ----------------
#
/*
$anzuzeigendeDaten[] = array(
    "tabellenname" => "mitglieder",
    "auswahltext" => "Mitglieder Stammdaten",
    "query" => "select * from mitglieder order by id desc;",
    "referenzqueries" => array(
        "Geschlecht" => "select id, geschlecht as anzeige from geschlechter order by geschlecht desc;",
        "Unternehmen" => "SELECT id, CONCAT(Name, ', ', Stadt) as anzeige from unternehmen order by Name;"
    ),
    "spaltenbreiten" => array(
        "Vorname"       => "120px",
        "Nachname"      => "120px",
        "Straße"        => "200px",
        "PLZ"           => "120px",
        "Wohnort"       => "200px",
        "Geschlecht"    => "40px"
    )
);
*/

$anzuzeigendeDaten[] = array(
    "tabellenname" => "bsv_1_verband",
    "auswahltext" => "Ortsverbände im LBSV Niedersachsen",
    "query" => "select id, auth_key, Name, Kurzname, Internetadresse from bsv_1_verband order by id desc;",
    "spaltenbreiten" => array(
        "auth_key"          => "250px",
        "Name"              => "350px",
        "Kurzname"          => "250px",
    )
);


$anzuzeigendeDaten[] = array(
    "tabellenname" => "log",
    "auswahltext" => "Log (zur Entwicklung)",
    "query" => "select id, id as `Nr.`, zeit as Timestamp, eintrag as Log from log order by id desc;",
    "spaltenbreiten" => array(
        "Nr."       => "80px",
        "Timestamp" => "220px"
    )
);

?>