<?php


# Regionalverbände
# Rechteverwaltung: Regionalverbände
# Log (zur Entwicklung)
# DEV-Mitglieder in den Sparten


######################################################################################################

# Regionalverbände
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_regionalverband",
    "auswahltext" => "Regionalverbände",
    "writeaccess" => true,
    "query" => "select id, BKV, Verband, Kurzname, Internetadresse from b_regionalverband order by id desc;",
    "spaltenbreiten" => array(
        "BKV"               => "100",
        "Verband"           => "400",
        "Kurzname"          => "200",
        "Internetadresse"   => "400"
    )
);

# Alle Verbände werden angezeigt
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_regionalverband_rechte",
    "auswahltext" => "Regionalverbände: Rechteverwaltung",
    "writeaccess" => true,
    "hinweis" => "Berechtigt angemeldete Nutzer, Sparten und BSG eines Verbandes zu sehen und zu bearbeiten.",
    "query" => "SELECT r.id as id, r.Verband as Verband, r.Nutzer
                FROM b_regionalverband_rechte as r 
                order by id desc;
                ",
    "referenzqueries" => array(
        "Verband" => "SELECT id, Verband as anzeige from b_regionalverband ORDER BY anzeige;",
        "Nutzer" => "SELECT id, mail as anzeige from y_user ORDER BY anzeige;"
    ),
    "spaltenbreiten" => array(
        "Verband"                       => "300",
        "Nutzer"                        => "300"
    )
);


$anzuzeigendeDaten['log'] = array(
    "tabellenname" => "log",
    "auswahltext" => "Log (zur Entwicklung)",
    "query" => "SELECT  id, id as Nr, zeit as Timestamp, eintrag as Log from log order by zeit desc;",
    "spaltenbreiten" => array(
        "Nr"        => "80",
        "Timestamp" => "220",
        "Log"       => "1620"
    )
);
$anzuzeigendeDaten['rollback'] = array(
    "tabellenname" => "log",
    "auswahltext" => "Rollback",
    "hinweis" => "Das Löschen und Anlegen neuer Mitglieder ist nicht im Rollback enthalten. Dies geschieht über den Berechtigungsmanager. Über die normale Schnittstelle sollten keine Mitglieder gelöscht werden können.",
    "query" => "SELECT  id, id as Nr, zeit as Timestamp, autor as Autor, eintrag as Query 
                from `rollback` order by zeit desc;",
    "spaltenbreiten" => array(
        "Nr"        => "80",
        "Timestamp" => "220",
        "Autor"     => "220",
        "Log"       => "600"
    )
);






?>