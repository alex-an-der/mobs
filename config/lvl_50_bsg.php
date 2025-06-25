<?php


/*
LOP

Karteileichen ausmisten:

SELECT yu.created, yu.lastlogin, yd.mail, b.BSG, yd.gebdatum, concat(yd.vname, ' ',yd.nname) as name
FROM y_v_userdata as yd
join y_user as yu on yu.id = yd.userID
join b_bsg as b on b.id = yd.bsg
LEFT JOIN b_mitglieder as m ON m.y_id = yd.userID
WHERE m.id IS NULL;

*/
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
        -- UNION ALL
        -- SELECT 0 as id, 'Bitte wählen...' as anzeige
        ORDER BY id;
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
    "auswahltext" => "Stamm-Mitglieder bearbeiten (kompakt)",
    "writeaccess" => true,
    "import" => false,
    "query" => "SELECT m.id as id, Vorname, Nachname, Bemerkung, BSG, aktiv
                from b_mitglieder as m
                WHERE 
                    (FIND_IN_SET(BSG, berechtigte_elemente($uid, 'BSG')) > 0 or 
                    ( BSG IS NULL AND FIND_IN_SET(m.id, berechtigte_elemente($uid, 'individuelle_mitglieder')) > 0))
                and m.BSG IS NOT NULL
                order by BSG, Vorname, Nachname;
    ",
    "referenzqueries" => array(
        "BSG" => "SELECT b.id, b.BSG as anzeige
        from b_bsg as b
        WHERE FIND_IN_SET(b.id, berechtigte_elemente($uid, 'BSG')) > 0
        ORDER BY anzeige;
        ",
        "aktiv" => "SELECT id, wert as anzeige
                        from b___an_aus;
        "
    ),
    "spaltenbreiten" => array(
        "BSG"                       => "300",
        "Bemerkung"                 => "300",
        "Vorname"                   => "150",
        "Nachname"                  => "150",
        "aktiv"                     => "100")
);

# Mitglieder in der Stamm-BSG bearbeiten
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_mitglieder",
    "auswahltext" => "Stamm-Mitglieder bearbeiten",
    "writeaccess" => true,
    "import" => false,
    "deleteanyway" => true,
    "query" => "SELECT m.id as id, m.id as info:Nr, Vorname, Nachname, Bemerkung, BSG, Mail, m.Geschlecht, m.Geburtsdatum, aktiv
                from b_mitglieder as m
                WHERE 
                    (FIND_IN_SET(BSG, berechtigte_elemente($uid, 'BSG')) > 0 or 
                    ( BSG IS NULL AND FIND_IN_SET(m.id, berechtigte_elemente($uid, 'individuelle_mitglieder')) > 0))
                and m.BSG IS NOT NULL
                order by BSG, Vorname, Nachname;
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
        "info:Nr"               => "70", 
        "BSG"                       => "300",
        "Bemerkung"                 => "300",
        "Vorname"                   => "150",
        "Nachname"                  => "150",
        "Mail"                      => "250",
        "Geschlecht"                => "100",
        "Geburtsdatum"              => "100",
        "aktiv"                     => "100"
    )
);


# Mitglieder in den Sparten 
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_mitglieder_in_sparten",
    "auswahltext" => "BSG-Mitglieder in Sparten anmelden", 
    "writeaccess" => true,
    "query" => "SELECT id, Mitglied as info:Mitglied, BSG as info:BSG, Sparte as info:Sparte 
                    from b_mitglieder_in_sparten as mis
                    WHERE FIND_IN_SET(mis.Mitglied, berechtigte_elemente($uid, 'individuelle_mitglieder')) > 0 and
                    FIND_IN_SET(BSG, berechtigte_elemente($uid, 'BSG')) > 0
                    order by mis.id desc;
    ",
    "referenzqueries" => array(
        "info:Mitglied" => "SELECT m.id as id, concat(m.Vorname,' ', m.Nachname, ' (Stamm: ', IFNULL(b.BSG, '".NULL_WERT."'), ')') as anzeige 
                        from b_mitglieder as m
                        left join b_bsg as b on m.BSG = b.id 
                        WHERE FIND_IN_SET(m.id, berechtigte_elemente($uid, 'individuelle_mitglieder')) > 0 
                        ORDER BY anzeige;
        ", // BSG is not null => Erst die Stamm BSG, dann die Sparten
        "info:BSG" => "SELECT b.id as id, concat(b.BSG,' (',v.Kurzname,')') as anzeige
                    from b_bsg as b
                    join b_regionalverband as v on v.id = b.Verband
                    WHERE
                        FIND_IN_SET(b.id, berechtigte_elemente($uid, 'BSG')) > 0;
        ",
        "info:Sparte" => "SELECT distinct s.id as id, concat (s.Sparte, ' (',r.Kurzname,')') as anzeige
                    FROM b_bsg_rechte as br
                    JOIN b_bsg as b ON br.BSG = b.id  
                    JOIN b_regionalverband as r ON r.id = b.Verband
                    JOIN b_sparte as s ON s.Verband = r.id
                    WHERE Nutzer = $uid 
                    ORDER BY anzeige;"
    ),
    // Stand 1.6.25: suchqueries OHNE info zuweisen (nach bestem Wissen)
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
    "query" => "SELECT mis.id, 
                concat(Vorname,' ', Nachname) as Mitglied , 
                stamm.BSG as Stamm_BSG , 
                s.Sparte as Sparte, 
                b.BSG as Sparten_BSG
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
        "Sparten-BSG"               => "400"
    )
);

$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_mitglieder_in_sparten",
    "auswahltext" => "$bericht Beiträge der Mitglieder",
    "writeaccess" => false,
    "query" => "SELECT * FROM 
                (SELECT m.id as id, b.VKZ, b.BSG, concat(m.Vorname, ' ', m.Nachname) as Name, 'Verbandsbeitrag' as Sparte, concat(r.Basisbeitrag, '€') as Beitrag
                from b_mitglieder as m
                join b_bsg as b on m.BSG=b.id
                join b_regionalverband as r on b.Verband = r.id

                union

                select m.id as id, b.VKZ, b.bsg, concat(m.Vorname, ' ', m.Nachname) as Name, s.Sparte, concat(s.Spartenbeitrag, '€') as Beitrag
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
        "Beitrag"                   => "150"
    )
);

$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_meldeliste",
    "auswahltext" => "$bericht Salden",
    "writeaccess" => false,
    "import" => false,
    "query" => "SELECT 
MAX(a.BSG) as id, -- Pflicht-id, hier nicht relevant
b.BSG as BSG,
a.Abrechnungsjahr,
SUM(a.HABEN) as HABEN,
SUM(a.SOLL) as SOLL,
(SUM(a.HABEN) - SUM(a.SOLL)) AS Saldo,
r.Verband as Empfaenger

FROM (
SELECT BSG_ID as BSG, Beitragsjahr as Abrechnungsjahr, Betrag as SOLL, 0 as HABEN, Beitragsstelle as Empfaenger
FROM b_meldeliste

UNION ALL

SELECT BSG, Abrechnungsjahr, 0 as SOLL, Haben as HABEN, Empfaenger
FROM b_zahlungseingaenge
) as a
JOIN b_bsg as b on b.id = a.BSG
JOIN b_regionalverband as r on a.Empfaenger = r.id

WHERE 
FIND_IN_SET(b.id, berechtigte_elemente($uid, 'bsg')) > 0 OR
FIND_IN_SET(b.Verband, berechtigte_elemente($uid, 'verband')) > 0 
GROUP BY BSG, Abrechnungsjahr, r.Verband;

    ",
    "spaltenbreiten" => array(
        "BSG"             => "300",
        "Abrechnungsjahr" => "120",
        "Soll"            => "100",
        "Haben"           => "100",
        "Saldo"           => "100",
        "Empfaenger"      => "300"
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
