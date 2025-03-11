<?php

$anzuzeigendeDaten['log'] = array(
    "tabellenname" => "log",
    "auswahltext" => "Error-Log",
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