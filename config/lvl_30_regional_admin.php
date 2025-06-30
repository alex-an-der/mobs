<?php
# Sparten im Regionalverband
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_sparte",
    "auswahltext" => "Sparten im Regionalverband einfügen, löschen und bearbeiten",
    "writeaccess" => true,
    "query" => "SELECT s.id as id, s.Verband as Verband, s.Sparte, Spartenbeitrag, s.Spartenleiter as Spartenleiter, s.Sportart as Sportart
                FROM b_sparte as s
                WHERE FIND_IN_SET(s.Verband, berechtigte_elemente(100, 'verband_erweitert')) > 0 
                order by id desc;
    ",
    "referenzqueries" => array(
        "Verband" => "SELECT v.id, v.Verband as anzeige
                        FROM b_regionalverband as v
                        WHERE FIND_IN_SET(v.id, berechtigte_elemente($uid, 'verband_erweitert')) > 0
                        ORDER BY anzeige;
        ",
        "Spartenleiter" => $mitgliederauswahl,
        "Sportart" => "SELECT id, CONCAT (Sportart,' (',Sportart_Nr,')') as anzeige from b___sportart ORDER BY anzeige;"
    ),
    "spaltenbreiten" => array(
        "Verband"                       => "380",
        "Sparte"                        => "250",  
        "Spartenbeitrag"                => "80",
        "Spartenleiter"                 => "250",  
        "Sportart"                      => "250"
    )
);

$curyear = (int)date("Y");

# Zahlungseingänge
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_zahlungseingaenge",
    "auswahltext" => "Zahlungseingänge ",
    "writeaccess" => true,
    "query" => "SELECT  z.id as id, z.BSG as BSG, z.Eingangsdatum, z.Abrechnungsjahr, z.Haben, z.Empfaenger
                FROM b_zahlungseingaenge as z
                JOIN b_bsg as b on b.id=z.BSG
                JOIN b_regionalverband as r on r.id = b.Verband 
                WHERE FIND_IN_SET(b.Verband, berechtigte_elemente($uid, 'verband_erweitert')) > 0
                ORDER BY Eingangsdatum desc;
    ",
    "referenzqueries" => array(
    "BSG" => "SELECT b.id as id, b.BSG as anzeige
                FROM b_bsg as b
                WHERE FIND_IN_SET(b.Verband, berechtigte_elemente($uid, 'verband_erweitert')) > 0
                ORDER BY anzeige;",
    "Abrechnungsjahr" => "SELECT 
                YEAR(CURDATE()) - 3 + n AS id,
                YEAR(CURDATE()) - 3 + n AS anzeige
                FROM 
                    (SELECT 0 AS n UNION ALL 
                    SELECT 1 AS n UNION ALL 
                    SELECT 2 AS n UNION ALL 
                    SELECT 3 AS n UNION ALL 
                    SELECT 4 AS n UNION ALL 
                    SELECT 5 AS n) AS years;",
    "Empfaenger" => "SELECT id, Verband as anzeige FROM b_regionalverband 
                     WHERE FIND_IN_SET(b_regionalverband.id, berechtigte_elemente($uid, 'verband_erweitert')) > 0
                     ORDER BY anzeige;"  
    ), // Empfaenger müssen auch ohne Berechtigung ausgewählt werden dürfen
       // Korrektur: Nein - jeder verwaltet ja nur seine Konten .. ?
    "spaltenbreiten" => array(
        "BSG"                          => "380",
        "Eingangsdatum"                => "150",
        "Abrechnungsjahr"              => "150",
        "Haben"                        => "150"
    ) 
);



## Notizen Offene Forderungen
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_forderungen",
    "auswahltext" => "Offene Forderungen (Notizen)",
    "writeaccess" => true,
    "query" => "SELECT f.id, f.Datum, f.BSG, f.Soll, f.Beschreibung
                FROM b_forderungen as f
                JOIN b_bsg as b on b.id=f.BSG
                WHERE FIND_IN_SET(b.Verband, berechtigte_elemente($uid, 'verband_erweitert')) > 0
                ORDER BY f.Datum desc;
    ",
    "referenzqueries" => array(
    "BSG" => "SELECT b.id as id, b.BSG as anzeige
                FROM b_bsg as b
                WHERE FIND_IN_SET(b.Verband, berechtigte_elemente($uid, 'verband_erweitert')) > 0
                ORDER BY anzeige;",
    ),
    "spaltenbreiten" => array(
        "Datum"                        => "170",
        "BSG"                          => "350",
        "Betrag"                       => "150",    
        "Beschreibung"                 => "600"
    ) 
);
?>