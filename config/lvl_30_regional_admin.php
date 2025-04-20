<?php
# Sparten im Regionalverband
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_sparte",
    "auswahltext" => "Sparten im Regionalverband einfügen, löschen und bearbeiten",
    "writeaccess" => true,
    "query" => "SELECT s.id as id, s.Verband as Verband, s.Sparte, Spartenbeitrag, s.Spartenleiter as Spartenleiter, s.Sportart as Sportart
        FROM b_sparte as s
        WHERE FIND_IN_SET(s.id, berechtigte_elemente($uid, 'sparte')) > 0 or Verband IS NULL
        order by id desc;
    ",
    "referenzqueries" => array(
        "Verband" => "SELECT v.id, v.Verband as anzeige
        FROM b_regionalverband as v
        WHERE FIND_IN_SET(v.id, berechtigte_elemente($uid, 'verband')) > 0
        ORDER BY anzeige;
        ",
        "Spartenleiter" => "SELECT m.id, CONCAT(Nachname, ', ', Vorname) as anzeige
        from b_mitglieder as m
        ORDER BY anzeige;",
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
?>