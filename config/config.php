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

$anzuzeigendeDaten[0] = array(
    "tabellenname" => "sparten",
    "auswahltext" => "Die BSV-Sparten",
    "query" => "select id, Sparte, CONCAT(REPLACE(Beitrag,'.',','),' €') as Beitrag, Leitung, Vertretung from sparten order by Sparte;",
    "hinweis" => "Hier sind alle Sparten und dies ist ein <b>Hinweistext</b>, was hier zu beachten ist.",
    "referenzqueries" => array(
        "Leitung"    => "select id, CONCAT(Vorname, ' ', Nachname) as anzeige from funktionaere order by Vorname;",
        "Vertretung" => "select id, CONCAT(Vorname, ' ', Nachname) as anzeige from funktionaere order by Vorname;"
    )
);

$anzuzeigendeDaten[1] = array(
    "tabellenname" => "sparten_mitglieder",
    "auswahltext" => "Sparten-Mitglieder",
    "query" => "select id, id as LfdNr, Sparte, Mitglied, Kommentar from sparten_mitglieder order by id desc;",
    "referenzqueries" => array(
        "Sparte" => "select id, Sparte as anzeige from sparten order by anzeige;",
        "Mitglied" => "select m.id as id, CONCAT(m.Nachname,', ',m.Vorname, ' (',u.Name,')') as anzeige from mitglieder as m left join unternehmen as u on m.Unternehmen=u.id order by m.Nachname;"
    )
);

?>