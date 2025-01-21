<?php 


# Rechteverwaltung: BSG
# Mitglieder in den Sparten 
# BSG im Regionalverband
# Sparten im Regionalverband

# Statistik: Mitglieder in Sparten


######################################################################################################

# BSG-Rechte - Wer darf die Mitglieder welcher BSG editieren? 
# Ich sehe nur BSG von Verbänden, zu deren Ansicht ich berechtigt bin
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_bsg_rechte",
    "auswahltext" => "Rechteverwaltung: BSG",
    "hinweis" => "Berechtigt angemeldete Nutzer, Mitglieder einer BSG zu sehen und zu bearbeiten.",
    "writeaccess" => true,
    "query" => "SELECT br.id as id, br.BSG, br.Nutzer
                from b_bsg_rechte as br
                left join v_verbands_berechtigte_bsg as vrb on br.BSG = vrb.BSG
                where vrb.Verbandsberechtigter = $uid OR br.BSG IS NULL;
                ",
    "referenzqueries" => array(
        "BSG" => "SELECT BSG as id, BSG_Name as anzeige
                    FROM v_verbands_berechtigte_bsg as vrb
                    where vrb.Verbandsberechtigter = $uid 
                    ORDER BY anzeige;",
        "Nutzer" => "SELECT id, mail as anzeige from y_user ORDER BY anzeige;"
    )
);

# Mitglieder in den Sparten 
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_mitglieder_in_sparten",
    "auswahltext" => "Mitglieder in den Sparten",
    "writeaccess" => true,
    "query" => "SELECT mis.id as id, mis.Sparte as Sparte, mis.Mitglied as Mitglied
                from b_mitglieder_in_sparten as mis
                left join v_verbands_berechtigte_sparte as vbs on vbs.Sparte = mis.Sparte
                where vbs.Verbandsberechtigter = $uid or mis.Sparte is NULL 
                order by mis.id desc;
    ",
    "referenzqueries" => array(
        "Sparte" => "SELECT Sparte as id, Sparte_Name as anzeige
                    from v_verbands_berechtigte_sparte
                    where Verbandsberechtigter = $uid
                    ORDER BY anzeige;
        ",
        "Mitglied" => "SELECT m.id as id, CONCAT(m.Nachname, ', ', m.Vorname, ' (', vbr.BSG_Name,')') as anzeige 
                        from b_mitglieder as m
                        join v_verbands_berechtigte_bsg as vbr on m.BSG = vbr.BSG
                        where vbr.Verbandsberechtigter = $uid
                        ORDER BY anzeige;
        "
    ),
    "suchqueries" => array(
        "Sparte" => "SELECT Sparte as id, Sparte_Name
                    from v_verbands_berechtigte_sparte
                    where Verbandsberechtigter = $uid;",
        "Mitglied" => "SELECT id, Vorname, Nachname, Mail from b_mitglieder as m join v_verbands_berechtigte_bsg as vbr on m.BSG = vbr.BSG where vbr.Verbandsberechtigter = $uid;"
    )
);


# BSG im Regionalverband
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_bsg",
    "auswahltext" => "BSG im Regionalverband",
    "writeaccess" => true,
    "hinweis" => "<b>RE </b> = Rechnungsempfänger. In diese Spalten bitte eintragen, wohin eventuelle Rechnungen geschickt werden sollen.",
    "query" => "SELECT 
        b.id as id,
        b.Verband as Verband,
        b.BSG as BSG,
        Ansprechpartner,
        RE_Name,
        RE_Name2,
        RE_Strasse_Nr,
        RE_Strasse2,
        RE_PLZ_Ort
        FROM b_bsg as b
        LEFT JOIN b_regionalverband_rechte as r on r.Verband = b.Verband
        WHERE b.Verband  IS NULL OR Nutzer = $uid
        order by id desc;
    ",
    "referenzqueries" => array(
        "Verband" => "SELECT v.id, v.Verband as anzeige
        from b_regionalverband as v
        join b_regionalverband_rechte as r on r.Verband = v.id 
        where r.Nutzer = $uid 
        ORDER BY anzeige;
        ",
        "Ansprechpartner" => "SELECT m.id, CONCAT(Nachname, ', ', Vorname) as anzeige 
                                from b_mitglieder as m
                                join b_bsg as b on b.id=m.BSG
                                join b_regionalverband_rechte as vr on b.Verband = vr.Verband
                                where vr.Nutzer = $uid
                                order by anzeige;"
    ),
    "spaltenbreiten" => array(
        "Verband"                       => "200",
        "BSG"                           => "200",  
        "Ansprechpartner"               => "400",  
        "RE_Name"                       => "200",  
        "RE_Name2"                      => "200",  
        "RE_Strasse_Nr"                 => "200",  
        "RE_Strasse2"                   => "200",  
        "RE_PLZ_Ort"                    => "200"
    ) 
);

# Sparten im Regionalverband
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_sparte",
    "auswahltext" => "Sparten im Regionalverband",
    "writeaccess" => true,
    "query" => "SELECT s.id as id, s.Verband as Verband, s.Sparte, s.Sportart as Sportart
        FROM b_sparte as s
        LEFT JOIN b_regionalverband_rechte as r on r.Verband = s.Verband
        WHERE r.Nutzer IS NULL OR r.Nutzer = $uid
        order by id desc;
    ",
    "referenzqueries" => array(
        "Verband" => "SELECT v.id, v.Verband as anzeige
        FROM b_regionalverband as v
        JOIN b_regionalverband_rechte as r on r.Verband = v.id 
        where r.Nutzer = $uid 
        ORDER BY anzeige;
        ",
        "Sportart" => "SELECT id, CONCAT (Sportart,' (',Sportart_Nr,')') as anzeige from b___sportart ORDER BY anzeige;"
    )
);

######################################################################################################

# Statistik: Mitglieder in Sparten
$statistik[] = array(
    "titel" => "Mitglieder in Sparten",
    "query" => "select s.Sparte, count(mis.Mitglied) as Mitglieder
                from b_mitglieder_in_sparten as mis
                join b_sparte as s on s.id = mis.Sparte
                join v_verbands_berechtigte_sparte as r on r.Sparte = s.id 
                where r.Verbandsberechtigter = $uid
                group by s.Sparte
                ",
    "typ"   => "torte"
);


?>