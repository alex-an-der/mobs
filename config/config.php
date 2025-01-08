<?php 
require_once(__DIR__ . "/../mods/all.head.php");
require_once(__DIR__ . "/../mods/config.head.php");
# Datenbank-Zugangsdaten
define("DB_NAME", "db_441127_14");
define("DB_HOST", "x96.lima-db.de");
define("DB_USER", "USER441127_bsadm");
define("DB_PASS", "BallBierBertha42");

$uid = $_SESSION['uid'];
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

# Verbände
$anzuzeigendeDaten[] = array(
    "tabellenname" => "bsv_1_verband",
    "auswahltext" => "Verbände im LBSV Niedersachsen",
    "query" => "select id, Name, Kurzname, Internetadresse from bsv_1_verband order by id desc;",
    "spaltenbreiten" => array(
        "Name"              => "350px",
        "Kurzname"          => "250px",
    )
);

#Verband-Rechte
$anzuzeigendeDaten[] = array(
    "tabellenname" => "bsv_1_verband_rechte",
    "auswahltext" => "Rechtemanagement: Verbände",
    "query" => "select id, berechtigter_yuser, Verband from bsv_1_verband_rechte order by id desc;",
    "referenzqueries" => array(
        "berechtigter_yuser" => "select id, mail as anzeige from y_user order by mail;",
        "Verband" => "SELECT id, `Name` as anzeige from bsv_1_verband order by Name;"
    )
);

/*
$anzuzeigendeDaten[] = array(
    "tabellenname" => "bsv_1_verband_rechte",
    "auswahltext" => "Rechtemanagement: Verbände",
    "query" => "select id, Nutzer, Verband from bsv_1_verband_rechte order by id desc;",
    "referenzqueries" => array(
        "Nutzer" => "select id, mail as anzeige from yuser order by mail;",
        "Verband" => "SELECT id, `Name` as anzeige from bsv_1_verband order by Name;"
    )
);*/

# BSG 
$anzuzeigendeDaten[] = array(
    "tabellenname" => "bsv_2_bsg",
    "auswahltext" => "BSG",
    "query" => "
        select b.id as id, b.Verband as Verband, b.Name as Name, b.Debitor as Debitor
        from bsv_1_verband_rechte as r
        join bsv_2_bsg as b
        on r.Verband = b.Verband
        WHERE r.berechtigter_yuser=$uid;",

    "referenzqueries" => array(

        "Verband" => "
            select v.id as id, v.Name as anzeige 
            from bsv_1_verband_rechte as r
            join bsv_1_verband as v
            on r.Verband = v.id
            WHERE r.berechtigter_yuser=$uid
            ORDER BY anzeige;
            "
        )
    
);

# Sparten
$anzuzeigendeDaten[] = array(
    "tabellenname" => "bsv_2_sparte",
    "auswahltext" => "Unsere Sparten",
    "query" => "
        select s.id as id, v.Name as Verband, s.Sparte as Sparte, s.Spartenleiter as Spartenleiter 
        from bsv_2_sparte_rechte as r
        join bsv_2_sparte as s  on r.Sparte = s.id
        join bsv_1_verband as v on s.Verband = v.id
        WHERE r.berechtigter_yuser=$uid 

        union

        select s.id as id, v.Name as Verband, s.Sparte as Sparte, s.Spartenleiter as Spartenleiter 
        from bsv_1_verband_rechte as r
        join bsv_2_sparte as s on r.Verband = s.Verband
        join bsv_1_verband as v on s.Verband = v.id
        WHERE r.berechtigter_yuser=$uid;"
);

# Sparten-Rechte
$anzuzeigendeDaten[] = array(
    "tabellenname" => "bsv_2_sparte_rechte",
    "auswahltext" => "Rechtemanagement: Sparten",
    "query" => "select id, Nutzer, Sparte from bsv_2_sparte_rechte order by id desc;",
    "referenzqueries" => array(
        "Nutzer" => "select id, geschlecht as anzeige from geschlechter order by geschlecht desc;",
        "Sparte" => "SELECT id, CONCAT(Name, ', ', Stadt) as anzeige from unternehmen order by Name;"
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