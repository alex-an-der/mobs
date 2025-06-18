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
    "tabellenname" => "sys_log",
    "auswahltext" => "Error-Log",
    "query" => "SELECT  id, id as Nr, zeit as Timestamp, eintrag as Log from sys_log order by zeit desc;",
    "spaltenbreiten" => array(
        "Nr"        => "80",
        "Timestamp" => "220",
        "Log"       => "1620"
    )
);
$anzuzeigendeDaten[] = array(
    "tabellenname" => "sys_rollback",
    "auswahltext" => "Rollback",
    "hinweis" => "Das Löschen und Anlegen neuer Mitglieder ist nicht im Rollback enthalten. Dies geschieht über den Berechtigungsmanager. Über die normale Schnittstelle sollten keine Mitglieder gelöscht werden können.",
    "query" => "SELECT  id, id as Nr, zeit as Timestamp, autor as Autor, eintrag as Query 
                from `sys_rollback` order by zeit desc;",
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

$anzuzeigendeDaten[] = array(
    "tabellenname" => "sys_error_manager",
    "auswahltext" => "Fehlermeldungen anpassen",
    "writeaccess" => true,
    "query" => "SELECT id, id as info:ID, error_log_id as info:error_log_id, source, raw_message, sql_error_code, add_fulltext_constraint, description, user_message 
                FROM sys_error_manager order by user_message asc",
    "spaltenbreiten" => array(
        "info:ID" => "20",
        "info:error_log_id" => "20",
        "source" => "120",
        "sql_error_code" => "120",
        "add_fulltext_constraint" => "100",
        "raw_message" => "500",
        "user_message" => "200",
        "description" => "200",
        
    )
);


?>