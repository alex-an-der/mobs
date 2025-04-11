<?php


# Regionalverbände
# Rechteverwaltung: Regionalverbände
# Log (zur Entwicklung)
# DEV-Mitglieder in den Sparten


######################################################################################################

# Regionalverbände
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_regionalverband",
    "auswahltext" => "Regionalverbände einfügen, löschen und bearbeiten",
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
    "auswahltext" => "Rechte zur Verwaltung in den Regionalverbänden vergeben",
    "writeaccess" => true,
    "hinweis" => "Berechtigt angemeldete Nutzer, Sparten und BSG eines Regionalverbandes zu sehen und zu bearbeiten. Eine neue Berechtigung kann mit <b>einf&uuml;gen</b> angelegt werden. Zur Bearbeitung der Sparten wird das zusätzliche Systemrecht 'Regional-Admin' benötigt.",
    "query" => "SELECT r.id as id, r.Verband as Verband, r.Nutzer
                FROM b_regionalverband_rechte as r 
                order by id desc;
                ",
    "referenzqueries" => array(
        "Verband" => "SELECT id, Verband as anzeige from b_regionalverband ORDER BY anzeige;",
        "Nutzer" => "SELECT y.id as id, concat (m.Vorname,' ',m.Nachname,', ' , b.BSG,', ',y.mail) as anzeige 
                        from y_user as y
                        join b_mitglieder as m on y.id = m.y_id 
                        join b_bsg as b on b.id = m.BSG
                        ORDER BY anzeige;"
                ),
    "spaltenbreiten" => array(
        "Verband"                       => "300",
        "Nutzer"                        => "300"
    )
);


?>