<?php 


# Sparten im Regionalverband
# BSG im Regionalverband
# Mitglieder in den Sparten 
# Rechteverwaltung: BSG

# Statistik: Mitglieder in Sparten


######################################################################################################

# BSG im Regionalverband
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_bsg",
    "auswahltext" => "BSG einfügen, löschen und bearbeiten",
    "writeaccess" => true,
    "query" => "SELECT 
        id, Verband, VKZ, BSG, 
        (SELECT COUNT(*) FROM b_mitglieder m WHERE m.BSG = b.id) AS `info:Mitglieder`,
        (SELECT COUNT(*) FROM b_bsg_rechte as r WHERE r.BSG = b.id) AS `info:Berechtigte`
        from b_bsg as b
        WHERE FIND_IN_SET(b.Verband, berechtigte_elemente($uid, 'verband')) > 0 
        order by id desc;
    ",
    "referenzqueries" => array(
        "Verband" => "SELECT v.id, v.Verband as anzeige
        from b_regionalverband as v
        WHERE FIND_IN_SET(v.id, berechtigte_elemente($uid, 'verband')) > 0
        ORDER BY anzeige;
        "
    ),
    "spaltenbreiten" => array(
        "Verband"                       => "380",
        "VKZ"                           => "100",
        "BSG"                           => "320",
        "info:Mitglieder"               => "100",
        "info:Berechtigte"              => "100"
    ) 
);

# BSG-Rechte - Wer darf die Mitglieder welcher BSG editieren? 
# Ich sehe nur BSG von Verbänden, zu deren Ansicht ich berechtigt bin
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_bsg_rechte",
    "auswahltext" => "Rechte zur Verwaltung von BSG vergeben",
    "hinweis" => "Berechtigt angemeldete Nutzer, Mitglieder einer BSG zu sehen und zu bearbeiten.",
    "writeaccess" => true,
    "query" => "SELECT br.id as id, br.BSG, br.Nutzer
                from b_bsg_rechte as br 
                left join b_bsg as b on br.BSG = b.id
                WHERE FIND_IN_SET(b.Verband, berechtigte_elemente($uid, 'verband')) > 0;
                ",
    "referenzqueries" => array(
        "BSG" => "SELECT b.id as id, b.BSG as anzeige
                    FROM b_bsg as b
                    WHERE FIND_IN_SET(b.Verband, berechtigte_elemente($uid, 'verband')) > 0
                    ORDER BY anzeige;",
        "Nutzer" => "SELECT y.id as id, concat (m.Vorname,' ',m.Nachname,', ' , IFNULL(b.BSG, '".NULL_WERT."'),', ',y.mail) as anzeige 
                        from y_user as y
                        join b_mitglieder as m on y.id = m.y_id 
                        left join b_bsg as b on b.id = m.BSG
                        ORDER BY anzeige;"
    ),
    "spaltenbreiten" => array(
        "BSG"                       => "300",
        "Nutzer"                    => "300"
    )
);


# BSG im Regionalverband
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_bsg",
    "auswahltext" => "$bericht BSG-Stammdaten",
    "import" => false,
    "writeaccess" => false,
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
        WHERE 
            FIND_IN_SET(b.Verband, berechtigte_elemente($uid, 'verband')) > 0 
        order by id desc;
    ",
    "referenzqueries" => array(
        "Ansprechpartner" => "SELECT m.id, CONCAT(Nachname, ', ', Vorname) as anzeige 
                                from b_mitglieder as m
                                join b_bsg as b on b.id=m.BSG
                                -- WHERE FIND_IN_SET(m.id, berechtigte_elemente($uid, 'mitglied')) > 0
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

# Mitglieder in Sparten
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_mitglieder_in_sparten",
    "auswahltext" => "$bericht Sparten und ihre Mitglieder",
    "writeaccess" => false,
    "query" => "SELECT mis.id as id, v.Kurzname as Verband, s.Sparte as Sparte, b.BSG as BSG, 
                    concat (m.Vorname,' ',m.Nachname,' (',m.id,')') as Mitglied
                from b_mitglieder_in_sparten as mis
                join b_sparte as s on mis.Sparte = s.id
                join b_regionalverband as v on s.Verband = v.id
                join b_mitglieder as m on mis.Mitglied = m.id
                join b_bsg as b on mis.BSG = b.id
                WHERE FIND_IN_SET(v.id, berechtigte_elemente ($uid, 'verband')) > 0
                order by Verband, Sparte, BSG, Mitglied;"
);



# Mitglieder nach Sportarten / Turniereinladungen
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_mitglieder_in_sparten",
    "auswahltext" => "$bericht Turniereinladungen",
    "writeaccess" => false,
    "import" => false,
    "hinweis" => "Es werden nur Mitglieder angezeigt, die ´Mailbenachrichtigung´ aktiviert haben.",
    "query" => "SELECT DISTINCT m.id as id, m.Mail as Mail, YEAR(m.Geburtsdatum) as Jahrgang, g.auswahl as 'm/w/d', sa.Sportart_Nr, sa.Sportart
                FROM b_mitglieder_in_sparten    as mis
                JOIN b_mitglieder               as m  ON m.id  = mis.Mitglied
                JOIN b_bsg                      as b  ON b.id  = m.BSG
                JOIN b_regionalverband          as r  ON r.id  = b.Verband
                JOIN b_sparte                   as s  ON s.id  = mis.Sparte 
                JOIN b___sportart               as sa ON sa.id = s.Sportart
                LEFT JOIN b___geschlecht        as g  ON g.id  = m.Geschlecht
                WHERE m.Mailbenachrichtigung=1"
);


$curyear = (int)date("Y");
# Mitglieder nach Sportarten / Turniereinladungen
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_mitglieder",
    "auswahltext" => "$bericht Salden ".$curyear,
    "writeaccess" => false,
    "import" => false,
    "query" => "SELECT 
                    b.id as id,
                    r.Kurzname as Verband,
                    b.BSG as BSG_Name,
                    ze.Haben as 'Haben/€',
                    IFNULL(SUM(abg.Gesamtbeitrag), 0) AS 'Soll/€',
                    IFNULL(ze.Haben, 0) - IFNULL(SUM(abg.Gesamtbeitrag),0) AS 'Saldo/€'
                FROM b_bsg as b
                JOIN b_regionalverband as r ON b.Verband = r.id
                LEFT JOIN (
                    -- Verbandsbeiträge + Spartenbeiträge
                    SELECT 
                        b.id AS BSG_ID,
                        CAST(REPLACE(r.Basisbeitrag, '€', '') AS DECIMAL(10,2)) AS Gesamtbeitrag
                    FROM b_mitglieder AS m
                    JOIN b_bsg AS b ON m.BSG = b.id
                    JOIN b_regionalverband AS r ON b.Verband = r.id
                    WHERE YEAR(m.Stammmitglied_seit) <= YEAR(CURDATE())
                    UNION ALL
                    SELECT 
                        b.id AS BSG_ID,
                        CAST(REPLACE(s.Spartenbeitrag, '€', '') AS DECIMAL(10,2)) AS Gesamtbeitrag
                    FROM b_mitglieder_in_sparten AS mis
                    JOIN b_sparte AS s ON mis.Sparte = s.id
                    JOIN b_mitglieder AS m ON mis.Mitglied = m.id
                    JOIN b_bsg AS b ON mis.BSG = b.id
                    WHERE YEAR(mis.seit) <= YEAR(CURDATE())
                ) AS abg ON abg.BSG_ID = b.id
                LEFT JOIN (
                    SELECT BSG, SUM(Haben) AS Haben
                    FROM b_zahlungseingaenge
                    WHERE Abrechnungsjahr = YEAR(CURDATE())
                    GROUP BY BSG
                ) AS ze ON b.id = ze.BSG
                WHERE FIND_IN_SET(r.id, berechtigte_elemente($uid, 'verband')) > 0
                GROUP BY b.id, r.Kurzname, b.BSG, ze.Haben
                ORDER BY b.BSG;
                ",
                "spaltenbreiten" => array(
                    "Verband"         => "150",
                    "BSG_Name"        => "300",
                    "Haben/€"         => "100",
                    "Soll/€"          => "100",
                    "Saldo/€"         => "100"
                )   
                
);

$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_mitglieder",
    "auswahltext" => "$bericht Salden ".($curyear-1),
    "writeaccess" => false,
    "import" => false,
    "query" => "SELECT 
                    abg.BSG_ID as id,
                    r.Kurzname as Verband,
                    abg.BSG_Name,
                    ze.Haben as 'Haben/€',
                    SUM(abg.Gesamtbeitrag) AS 'Soll/€',
                    IFNULL(ze.Haben, 0) - IFNULL(SUM(abg.Gesamtbeitrag),0) AS 'Saldo/€'
                FROM (
                    -- Verbandsbeiträge
                    SELECT 
                        b.id AS BSG_ID,
                        b.BSG AS BSG_Name,
                        CAST(REPLACE(r.Basisbeitrag, '€', '') AS DECIMAL(10,2)) AS Gesamtbeitrag
                    FROM b_mitglieder AS m
                    JOIN b_bsg AS b ON m.BSG = b.id
                    JOIN b_regionalverband AS r ON b.Verband = r.id
                    WHERE YEAR(m.Stammmitglied_seit) <= YEAR(CURDATE()) -1

                    UNION ALL

                    -- Spartenbeiträge
                    SELECT 
                        b.id AS BSG_ID,
                        b.BSG AS BSG_Name,
                        CAST(REPLACE(s.Spartenbeitrag, '€', '') AS DECIMAL(10,2)) AS Gesamtbeitrag
                    FROM b_mitglieder_in_sparten AS mis
                    JOIN b_sparte AS s ON mis.Sparte = s.id
                    JOIN b_mitglieder AS m ON mis.Mitglied = m.id
                    JOIN b_bsg AS b ON mis.BSG = b.id
                    WHERE YEAR(mis.seit) <= YEAR(CURDATE()) -1
                ) AS abg
                LEFT JOIN (
                    SELECT BSG, SUM(Haben) AS Haben
                    FROM b_zahlungseingaenge
                    WHERE Abrechnungsjahr = YEAR(CURDATE()) -1
                    GROUP BY BSG
                ) AS ze ON abg.BSG_ID = ze.BSG
                JOIN b_bsg as b ON b.id = abg.BSG_ID
                JOIN b_regionalverband as r ON b.Verband = r.id
                WHERE FIND_IN_SET(r.id, berechtigte_elemente($uid, 'verband')) > 0
                GROUP BY abg.BSG_ID, r.Kurzname, abg.BSG_Name, ze.Haben
                ORDER BY abg.BSG_Name;
                ",
                "spaltenbreiten" => array(
                    "Verband"         => "150",
                    "BSG_Name"        => "300",
                    "Haben/€"         => "100",
                    "Soll/€"          => "100",
                    "Saldo/€"         => "100"
                )   
);


$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_v_meldeliste_dieses_jahr",
    "auswahltext" => "$bericht Meldeliste ".$curyear." auf Ebene Regionalverband",
    "writeaccess" => false,
    "import" => false,
    "query" => "SELECT
        rv_id           as id,
        Erfasst_am           ,
        Beitragsjahr         ,
        Mitglied             ,
        Zahlungspflichtige_BSG,
        Zuordnung            ,
        Beschreibung         ,
        Betrag               

                FROM b_v_meldeliste_dieses_jahr
                WHERE FIND_IN_SET(rv_id, berechtigte_elemente($uid, 'verband')) > 0
                ORDER BY Erfasst_am DESC;",
    "spaltenbreiten" => array(
        "Erfasst_am"              => "200",
        "Beitragsjahr"            => "100",
        "Mitglied"                => "250",
        "Zahlungspflichtige_BSG"  => "200",
        "Zuordnung"               => "150",
        "Beschreibung"            => "150",
        "Betrag"                  => "100",
        )
);

$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_v_meldeliste_letztes_jahr",
    "auswahltext" => "$bericht Meldeliste ".($curyear-1)." auf Ebene Regionalverband",
    "writeaccess" => false,
    "import" => false,
    "query" => "SELECT
        rv_id           as id,
        Erfasst_am           ,
        Beitragsjahr         ,
        Mitglied             ,
        Zahlungspflichtige_BSG,
        Zuordnung            ,
        Beschreibung         ,
        Betrag               

                FROM b_v_meldeliste_letztes_jahr
                WHERE FIND_IN_SET(rv_id, berechtigte_elemente($uid, 'verband')) > 0
                ORDER BY Erfasst_am DESC;",
    "spaltenbreiten" => array(
        "Erfasst_am"              => "200",
        "Beitragsjahr"            => "100",
        "Mitglied"                => "250",
        "Zahlungspflichtige_BSG"  => "200",
        "Zuordnung"               => "150",
        "Beschreibung"            => "150",
        "Betrag"                  => "100",
        )
);

# Änderungshistorie per Mitglied
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_mitglieder_historie",
    "auswahltext" => "$bericht Änderungen per Mitgliedsnummer",
    "writeaccess" => false,
    "import" => false,
    "query" => "SELECT id, Timestamp, MNr, Aktion 
                FROM b_mitglieder_historie
                WHERE FIND_IN_SET(MNr, berechtigte_elemente($uid, 'mitglied')) > 0
                ORDER BY Timestamp DESC;",
    "spaltenbreiten" => array(
        "Timestamp" => "150",
        "MNr"       => "100",
        "Aktion"    => "300"
        )
);

######################################################################################################

# Statistik: Mitglieder in Sparten
$statistik[] = array(
    "titel" => "Mitglieder in Sparten im Regionalverband",
    "query" => "SELECT s.Sparte, count(mis.Mitglied) as Mitglieder
                from b_mitglieder_in_sparten as mis
                join b_sparte as s on s.id = mis.Sparte
                WHERE FIND_IN_SET(s.id, berechtigte_elemente($uid, 'sparte')) > 0
                group by s.Sparte;
                ",
    "typ"   => "torte"
);

 

?>

