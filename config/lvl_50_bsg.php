<?php


# Mitglieder in der BSG

######################################################################################################

$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_bsg",
    "auswahltext" => "BSG-Stammdaten bearbeiten",
    "import" => false,
    "writeaccess" => true,
    "hinweis" => "<b>RE </b> = Rechnungsempfänger. In diese Spalten bitte eintragen, wohin eventuelle Rechnungen geschickt werden sollen.",
    "query" => "SELECT 
        b.id as id,
        b.BSG as BSG,
        Ansprechpartner,
        RE_Name,
        RE_Name2,
        RE_Strasse_Nr,
        RE_Strasse2,
        RE_PLZ_Ort
        FROM b_bsg as b
        WHERE FIND_IN_SET(b.id, berechtigte_elemente($uid, 'BSG')) > 0
        order by id desc;
    ",
    "referenzqueries" => array(
        "Ansprechpartner" => "SELECT m.id, CONCAT(Nachname, ', ', Vorname) as anzeige 
            from b_mitglieder as m
            left join b_bsg as b on b.id=m.BSG
            WHERE FIND_IN_SET(m.id, berechtigte_elemente($uid, 'mitglied')) > 0 OR
            FIND_IN_SET(m.id, berechtigte_elemente($uid, 'individuelle_mitglieder')) > 0
            order by anzeige;"
    ),
    "spaltenbreiten" => array(
        "Verband"                       => "380",
        "BSG"                           => "320",  
        "Ansprechpartner"               => "200",  
        "RE_Name"                       => "200",  
        "RE_Name2"                      => "200",  
        "RE_Strasse_Nr"                 => "200",  
        "RE_Strasse2"                   => "200",  
        "RE_PLZ_Ort"                    => "200"
    ) 
);


# Mitglieder in der Stamm-BSG
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_mitglieder",
    "auswahltext" => "BSG- (Stamm-) Mitglieder bearbeiten",
    "writeaccess" => true,
    "import" => false,
    "query" => "SELECT m.id as id, BSG, Vorname, Nachname, Mail, m.Geschlecht, m.Geburtsdatum, aktiv
                from b_mitglieder as m
                WHERE FIND_IN_SET(BSG, berechtigte_elemente($uid, 'BSG')) > 0 or 
                ( BSG IS NULL AND FIND_IN_SET(m.id, berechtigte_elemente($uid, 'individuelle_mitglieder')) > 0)
                order by id desc;
    ",
    "referenzqueries" => array(
        "BSG" => "SELECT b.id, b.BSG as anzeige
        from b_bsg as b
        WHERE FIND_IN_SET(b.id, berechtigte_elemente($uid, 'BSG')) > 0
        ORDER BY anzeige;
        ",
        "Geschlecht" => "SELECT id, auswahl as anzeige
                        from b___geschlecht;
        ",
        "aktiv" => "SELECT id, wert as anzeige
                        from b___an_aus;
        "
    ),
    "spaltenbreiten" => array(
        "BSG"                       => "300",
        "Vorname"                   => "150",
        "Nachname"                  => "150",
        "Mail"                      => "250"
    )
);


# Mitglieder in den Sparten 
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_mitglieder_in_sparten",
    "auswahltext" => "BSG-Mitglieder in Sparten anmelden",
    "writeaccess" => true,

    "query" => "SELECT id, Mitglied, BSG, Sparte 
                    from b_mitglieder_in_sparten as mis
                    WHERE FIND_IN_SET(mis.Mitglied, berechtigte_elemente($uid, 'individuelle_mitglieder')) > 0 and
                    FIND_IN_SET(BSG, berechtigte_elemente($uid, 'BSG')) > 0
                    order by mis.id desc;
    ",
    "referenzqueries" => array(
        "Mitglied" => "SELECT m.id as id, concat(m.Vorname,' ', m.Nachname, ' (Stamm: ', b.BSG,')') as anzeige 
                        from b_mitglieder as m
                        join b_bsg as b on m.BSG = b.id 
                        WHERE FIND_IN_SET(m.id, berechtigte_elemente($uid, 'individuelle_mitglieder')) > 0 
                        AND m.BSG is not null
                        ORDER BY anzeige;
        ", // BSG is not null => Erst die Stamm BSG, dann die Sparten
        "BSG" => "SELECT b.id as id, concat(b.BSG,' (',v.Kurzname,')') as anzeige
                    from b_bsg as b
                    join b_regionalverband as v on v.id = b.Verband
                    WHERE
                        FIND_IN_SET(b.id, berechtigte_elemente($uid, 'BSG')) > 0;
        ",
        "Sparte" => "SELECT distinct s.id as id, concat (s.Sparte, ' (',r.Kurzname,')') as anzeige
                    FROM b_bsg_rechte as br
                    JOIN b_bsg as b ON br.BSG = b.id  
                    JOIN b_regionalverband as r ON r.id = b.Verband
                    JOIN b_sparte as s ON s.Verband = r.id
                    WHERE Nutzer = $uid 
                    ORDER BY anzeige;"
    ),
    "suchqueries" => array(
        "Sparte" => "SELECT s.id, s.Sparte, v.Verband, v.Kurzname
                    from b_sparte as s
                    join b_regionalverband as v on s.Verband = v.id
                    WHERE FIND_IN_SET(s.id, berechtigte_elemente($uid, 'sparte')) > 0",
        "Mitglied" => "SELECT id, Vorname, Nachname, Mail 
                        from b_mitglieder as m 
                        WHERE FIND_IN_SET(id, berechtigte_elemente($uid, 'mitglied')) > 0
        "
    ),
    "spaltenbreiten" => array(
        "Mitglied"                  => "400",
        "BSG"                       => "400",
        "Sparte"                    => "300"
    )
);

$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_mitglieder_in_sparten",
    "auswahltext" => "$bericht Mitglieder und ihre Sparten",
    "writeaccess" => false,
    "query" => "SELECT mis.id, concat(Vorname,' ', Nachname) as Mitglied , stamm.BSG as Stamm_BSG , s.Sparte as Sparte, b.BSG as Sparten_BSG
                from b_mitglieder_in_sparten as mis
                join b_mitglieder as m on mis.Mitglied = m.id
                join b_bsg as b on mis.BSG = b.id
                join b_regionalverband as v on b.Verband = v.id
                join b_sparte as s on mis.Sparte = s.id
                join b_bsg as stamm on m.BSG = stamm.id
                WHERE FIND_IN_SET(mis.Mitglied, berechtigte_elemente($uid, 'individuelle_mitglieder')) > 0
    ",
    "spaltenbreiten" => array(
        "Mitglied"                  => "400",
        "Stamm-BSG"                 => "400",
        "Sparte"                    => "300",
        "Sparten-BSG"               => "400",
    )
);


######################################################################################################

# Statistik: Mitglieder in Sparten
$statistik[] = array(
    "titel" => "Mitglieder in Sparten in meinen BSG",
    "query" => "SELECT s.Sparte, count(mis.Mitglied) as Mitglieder
                from b_mitglieder_in_sparten as mis
                join b_sparte as s on s.id = mis.Sparte
                join b_mitglieder as m on m.id = mis.Mitglied
                join b_bsg_rechte as r on r.BSG = m.BSG
                where r.Nutzer = $uid
                group by s.Sparte
                ",
    "typ"   => "torte"
);
?>
