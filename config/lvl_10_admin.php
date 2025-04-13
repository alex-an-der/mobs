<?php

$anzuzeigendeDaten[] = array(
    "tabellenname" => "adm_issues",
    "auswahltext" => "Offene Issues",
    "writeaccess" => true,
    "query" => "SELECT  id, version, Prio, Issue, Kommentar FROM adm_issues order by Prio ASC;",
    "spaltenbreiten"    => array(
        "version"       => "100",
        "Prio"          => "50",
        "Issue"         => "450",
        "Kommentar"     => "200"
    )
);

$anzuzeigendeDaten[] = array(
    "tabellenname" => "adm_log",
    "auswahltext" => "Error-Log",
    "query" => "SELECT  id, id as Nr, zeit as Timestamp, eintrag as Log from adm_log order by zeit desc;",
    "spaltenbreiten" => array(
        "Nr"        => "80",
        "Timestamp" => "220",
        "Log"       => "1620"
    )
);
$anzuzeigendeDaten[] = array(
    "tabellenname" => "adm_rollback",
    "auswahltext" => "Rollback",
    "hinweis" => "Das Löschen und Anlegen neuer Mitglieder ist nicht im Rollback enthalten. Dies geschieht über den Berechtigungsmanager. Über die normale Schnittstelle sollten keine Mitglieder gelöscht werden können.",
    "query" => "SELECT  id, id as Nr, zeit as Timestamp, autor as Autor, eintrag as Query 
                from `adm_rollback` order by zeit desc;",
    "spaltenbreiten" => array(
        "Nr"        => "80",
        "Timestamp" => "220",
        "Autor"     => "220",
        "Query"     => "600"
    )
);

$anzuzeigendeDaten[] = array(
    "tabellenname" => "adm_usercount",
    "auswahltext" => "Nutzerzahlen",
    "query" => "select id, Timestamp, Anzahl from adm_usercount order by Timestamp desc;",
    "spaltenbreiten" => array(
        "Nr"        => "80",
        "Timestamp" => "220",
        "Log"       => "1620"
    )
);
?>