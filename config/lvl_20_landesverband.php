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
    "query" => "select id, BKV, Verband, Kurzname, Basisbeitrag, Internetadresse from b_regionalverband order by id desc;",
    "spaltenbreiten" => array(
        "BKV"               => "100",
        "Verband"           => "400",
        "Kurzname"          => "200",
        "Basisbeitrag"      => "80",
        "Internetadresse"   => "400"
    )
);

# Alle Verbände werden angezeigt
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_regionalverband_rechte",
    "auswahltext" => "Rechte zur Verwaltung in den Regionalverbänden vergeben",
    "writeaccess" => true,
    "hinweis" => "Berechtigt angemeldete Nutzer, Sparten und BSG eines Regionalverbandes zu sehen und zu bearbeiten. Eine neue Berechtigung kann mit <b>einf&uuml;gen</b> angelegt werden. Zur Bearbeitung der Sparten wird das zusätzliche Systemrecht 'Regional-Admin' benötigt.",
    "query" => "SELECT r.id as id, r.Verband as Verband, r.Nutzer, r.erweiterte_Rechte as erweiterte_Rechte
                FROM b_regionalverband_rechte as r 
                order by id desc;
                ",
    "referenzqueries" => array(
    "Verband"               => "SELECT id, Verband as anzeige from b_regionalverband ORDER BY anzeige;",
    "Nutzer"                => "SELECT y.id as id, $mitgliederconcat as anzeige 
                                    from y_user as y
                                    join b_mitglieder as m on y.id = m.y_id 
                                    left join b_bsg as b on b.id = m.BSG
                                    ORDER BY anzeige;",
    "erweiterte_Rechte"     => "SELECT id, wert as anzeige from b___an_aus ORDER BY id;",                
                ),
    "spaltenbreiten" => array(
        "Verband"                       => "300",
        "Nutzer"                        => "300",
        "erweiterte_Rechte"             => "100"
    )
);

$rechteuebersicht = array(
    "tabellenname" => "b_regionalverband_rechte",
    "auswahltext" => "$bericht Rechte-Übersicht",
    "writeaccess" => false,
    "query" => "SELECT 1 as id, $mitgliederconcat as Mitglied, Typ, Berechtigung FROM
                    (SELECT Nutzer, 'Verband' as Typ, v.Verband as Berechtigung
                    FROM b_regionalverband_rechte as r 
                    JOIN b_regionalverband as v on v.id=r.Verband

                    UNION ALL

                    SELECT Nutzer, 'BSG' as Typ, CONCAT(bb.BSG, ' (', v.Verband,')') as Berechtigung
                    FROM b_bsg_rechte as r 
                    JOIN b_bsg as bb on bb.id=r.BSG
                    JOIN b_regionalverband as v on v.id=bb.Verband) as rechte
                    JOIN b_mitglieder as m on m.y_id = rechte.Nutzer
                    JOIN b_bsg as b on b.id = m.bsg    
                    
                ",
    "spaltenbreiten" => array(
        "Verband"                       => "300",
        "Nutzer"                        => "300",
        "erweiterte_Rechte"             => "100"
    )
);
$anzuzeigendeDaten[] = $rechteuebersicht;

?>