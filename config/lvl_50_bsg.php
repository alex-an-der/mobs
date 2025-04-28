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

# Mitglieder in die Stamm-BSG aufnehmen
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_mitglieder",
    "auswahltext" => "Stamm-Mitglieder aufnehmen",
    "writeaccess" => true,
    "import" => false,
    "query" => "SELECT 
                m.id as id, 
                concat(m.Vorname,' ',  m.Nachname, ' (', DATE_FORMAT(m.Geburtsdatum, '%d.%m.%Y') , ')') as info:Mitglied, 
                b.BSG as 'info:will_nach' , 
                m.BSG 
                FROM b_mitglieder as m 
                JOIN b_bsg_wechselantrag as wa ON m.id = wa.m_id
                JOIN b_bsg as b ON b.id = wa.Ziel_BSG
                WHERE FIND_IN_SET(wa.Ziel_BSG, berechtigte_elemente($uid, 'bsg')) > 0 
                AND IFNULL(m.BSG,-1) != IFNULL(wa.Ziel_BSG, -2);",

    "referenzqueries" => array(
        "BSG" => "SELECT b.id, b.BSG as anzeige
        from b_bsg as b
        WHERE FIND_IN_SET(b.id, berechtigte_elemente($uid, 'BSG')) > 0
        ORDER BY anzeige;
        ",
    ),
    "spaltenbreiten" => array(

        "info:Vorname"              => "150",
        "info:Nachname"             => "150",
        "BSG"                       => "300",
        "info:Mail"                 => "250",
        "info:Geburtsdatum"         => "100",
    )
);

# Mitglieder in der Stamm-BSG bearbeiten
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_mitglieder",
    "auswahltext" => "Stamm-Mitglieder bearbeiten",
    "hinweis" => "<b>ACHTUNG!</b> Das Feld <b>Stammmitglied_seit</b> wird <b>automatisch</b> angepasst, wenn sich die BSG ändert. Dies wird erst 
    nach dem erneuten Laden sichtbar und kann dann manuell verändert werden. Dieses Angabe dient nur zur Information und  wird bei der Rechnungsstellung nicht berücksichtigt.",
    "writeaccess" => true,
    "import" => false,
    "query" => "SELECT m.id as id, Vorname, Nachname, BSG, Stammmitglied_seit, Mail, m.Geschlecht, m.Geburtsdatum, aktiv
                from b_mitglieder as m
                WHERE 
                    (FIND_IN_SET(BSG, berechtigte_elemente($uid, 'BSG')) > 0 or 
                    ( BSG IS NULL AND FIND_IN_SET(m.id, berechtigte_elemente($uid, 'individuelle_mitglieder')) > 0))
                and m.BSG IS NOT NULL
                order by BSG, Vorname desc;
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
        "Mail"                      => "250",
        "Geschlecht"                => "100",
        "Geburtsdatum"              => "100",
        "aktiv"                     => "100",
        "Stammmitglied_seit"        => "100",
    )
);


# Mitglieder in den Sparten 
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_mitglieder_in_sparten",
    "auswahltext" => "BSG-Mitglieder in Sparten anmelden",
    "hinweis" => "Das Feld ´<b>seit</b>´ wird <b>automatisch</b> bei einem neuen Eintrag gesetzt und dient nur zur Information. 
    Das Feld wird nicht für die Rechnungsstellung genutzt.", 
    "writeaccess" => true,
    "query" => "SELECT id, Mitglied, BSG, Sparte, DATE_FORMAT(seit, '%d.%m.%Y') as info:seit
                    from b_mitglieder_in_sparten as mis
                    WHERE FIND_IN_SET(mis.Mitglied, berechtigte_elemente($uid, 'individuelle_mitglieder')) > 0 and
                    FIND_IN_SET(BSG, berechtigte_elemente($uid, 'BSG')) > 0
                    order by mis.id desc;
    ",
    "referenzqueries" => array(
        "Mitglied" => "SELECT m.id as id, concat(m.Vorname,' ', m.Nachname, ' (Stamm: ', IFNULL(b.BSG, '".NULL_WERT."'), ')') as anzeige 
                        from b_mitglieder as m
                        left join b_bsg as b on m.BSG = b.id 
                        WHERE FIND_IN_SET(m.id, berechtigte_elemente($uid, 'individuelle_mitglieder')) > 0 
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
        "Sparte"                    => "300",
        "info:seit"                 => "100"
    )
);


$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_mitglieder_in_sparten",
    "auswahltext" => "$bericht Mitglieder und ihre Sparten",
    "writeaccess" => false,
    "query" => "SELECT mis.id, 
                concat(Vorname,' ', Nachname) as Mitglied , 
                stamm.BSG as Stamm_BSG , 
                s.Sparte as Sparte, 
                b.BSG as Sparten_BSG, 
                DATE_FORMAT(mis.seit, '%d.%m.%Y') as seit
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
        "seit"                      => "150"
    )
);

$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_mitglieder_in_sparten",
    "auswahltext" => "$bericht Beiträge der Mitglieder",
    "writeaccess" => false,
    "query" => "SELECT * FROM 
                (SELECT m.id as id, b.VKZ, b.BSG, concat(m.Vorname, ' ', m.Nachname) as Name, 'Verbandsbeitrag' as Sparte, concat(r.Basisbeitrag, '€') as Beitrag, DATE_FORMAT(m.Stammmitglied_seit, '%d.%m.%Y') as seit
                from b_mitglieder as m
                join b_bsg as b on m.BSG=b.id
                join b_regionalverband as r on b.Verband = r.id

                union

                select m.id as id, b.VKZ, b.bsg, concat(m.Vorname, ' ', m.Nachname) as Name, s.Sparte, concat(s.Spartenbeitrag, '€') as Beitrag,  DATE_FORMAT(mis.seit, '%d.%m.%Y') as seit
                from b_mitglieder_in_sparten as mis 
                join b_sparte as s on mis.Sparte = s.id
                join b_mitglieder as m on mis.Mitglied = m.id
                join b_bsg as b on mis.BSG=b.id) AS bigsel
                WHERE FIND_IN_SET(id, berechtigte_elemente($uid, 'individuelle_mitglieder')) > 0
                order by Name;
                
    ",
    "spaltenbreiten" => array(
        "VKZ"                       => "100",
        "BSG"                       => "300",
        "Name"                      => "400",
        "Sparte"                    => "300",
        "Beitrag"                   => "150",
        "seit"                      => "150"
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
