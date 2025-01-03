<?php 
# Datenbank-Zugangsdaten
define("DB_NAME", "db_441127_12");
define("DB_HOST", "x96.lima-db.de");
define("DB_USER", "USER441127");
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
# $anzuzeigendeDaten[] = array(
#     "tabellenname" => "sparten",
#     "auswahltext" => "Die BSV-Sparten",
#     "query" => "select id, Sparte, CONCAT(REPLACE(Beitrag,'.',','),' €') as Beitrag, Leitung, Vertretung from sparten order by Sparte;",
#     "hinweis" => "Hier sind alle Sparten und dies ist ein <b>Hinweistext</b>, was hier zu beachten ist.",
#     "referenzqueries" => array(
#         "Leitung"    => "select id, CONCAT(Vorname, ' ', Nachname) as anzeige from funktionaere order by Vorname;",
#         "Vertretung" => "select id, CONCAT(Vorname, ' ', Nachname) as anzeige from funktionaere order by Vorname;"
#     )
# );

$anzuzeigendeDaten[] = array(
    "tabellenname" => "kommentare",
    "auswahltext" => "!! Bitte hier kommentieren, was euch gefällt oder nicht gefällt.",
    "query" => "select id, timestamp as Zeitstempel, Kommentar, Autor from kommentare order by id desc;"
);

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

$anzuzeigendeDaten[] = array(
    "tabellenname" => "sparten",
    "auswahltext" => "Die BSV-Sparten",
    "query" => "select id, Sparte, CONCAT(REPLACE(Beitrag,'.',','),' €') as Beitrag, Leitung, Vertretung from sparten order by Sparte;",
    "referenzqueries" => array(
        "Leitung"    => "select id, CONCAT(Vorname, ' ', Nachname) as anzeige from funktionaere order by Vorname;",
        "Vertretung" => "select id, CONCAT(Vorname, ' ', Nachname) as anzeige from funktionaere order by Vorname;"
    )
);

$anzuzeigendeDaten[] = array(
    "tabellenname" => "sparten_mitglieder",
    "auswahltext" => "Sparten-Mitglieder",
    "query" => "select id, Sparte, Mitglied, Kommentar from sparten_mitglieder order by id desc;",
    "referenzqueries" => array(
        "Sparte" => "select id, Sparte as anzeige from sparten order by anzeige;",
        "Mitglied" => "select m.id as id, CONCAT(m.Nachname,', ',m.Vorname, ' (',u.Name,')') as anzeige from mitglieder as m left join unternehmen as u on m.Unternehmen=u.id order by m.Nachname;"
    )
);

$anzuzeigendeDaten[] = array(
    "tabellenname" => "unternehmen",
    "auswahltext" => "Unternehmen, Behörden und Betriebssportgemeinschaften",
    "query" => "select * from unternehmen order by id desc;",
    "hinweis" => "Dies kann später noch ergänzt werden z.B. mit einer Flag 'natürliche Person (j/n)' - dazu muss ich aber zuerst noch den Datentyp 'binär' implementieren."
);

$anzuzeigendeDaten[] = array(
    "tabellenname" => "funktionaere",
    "auswahltext" => "Amtsträger",
    "query" => "select * from funktionaere order by id desc;",
    "hinweis" => "Dies kann später noch ergänzt werden z.B. mit einer Flag 'Mitglied des erweiterten Vorstandes' - dazu muss ich aber zuerst noch den Datentyp 'binär' implementieren.",
    "referenzqueries" => array(
        "Leitung"    => "select id, CONCAT(Vorname, ' ', Nachname) as anzeige from funktionaere order by Vorname;",
        "Vertretung" => "select id, CONCAT(Vorname, ' ', Nachname) as anzeige from funktionaere order by Vorname;"
    )
);

$anzuzeigendeDaten[] = array(
    "tabellenname" => "log",
    "auswahltext" => "Log (zur Entwicklung)",
    "query" => "select id, id as `Nr.`, zeit as Timestamp, eintrag as Log from log order by id desc;"
);

?>