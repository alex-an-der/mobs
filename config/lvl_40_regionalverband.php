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
    "hinweis" => "Es können nur BSG ohne Mitglieder gelöscht werden! Bitte vor dem löschen die Mitglieder entweder löschen oder in andere BSG transferieren.",
    "writeaccess" => true,
    "query" => "SELECT 
        id, Verband, VKZ, BSG
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


$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_meldeliste",
    "auswahltext" => "$bericht Meldeliste $curyear",
    "writeaccess" => false,
    "import" => false,
    "query" => "SELECT 
            subsel.id, 
            DATE_FORMAT(Timestamp, '%d.%m.%Y') AS Erfasst_am, 
            Beitragsjahr,
            Mitglied, 
            BSG as Zahlungspflichtige_BSG, 
            Kategorie, 
            Zuordnung,
            Betrag as €,
            rv.Kurzname as Beitragsstelle
            FROM  
            (SELECT ml.id as id, Timestamp, ml.Beitragsjahr, ml.Mitglied, ml.BSG, bz.Zweck as Kategorie, rv.Kurzname as Zuordnung, ml.Beitragsjahr as jahr, ml.Beitragsstelle as Beitragsstelle, Betrag
                FROM b_meldeliste as ml
                JOIN b___beitragszuordnungen as bz on ml.Zuordnung = bz.id
                JOIN b_regionalverband as rv on rv.id = ml.Zuordnung_ID
                WHERE bz.id = 1
                
                UNION ALL 
                
                SELECT ml.id as id, Timestamp, ml.Beitragsjahr, ml.Mitglied, ml.BSG, bz.Zweck as Kategorie, sp.Sparte as Zuordnung, ml.Beitragsjahr as jahr, ml.Beitragsstelle as Beitragsstelle, Betrag
                FROM b_meldeliste as ml
                JOIN b___beitragszuordnungen as bz on ml.Zuordnung = bz.id
                JOIN b_sparte as sp on sp.id = ml.Zuordnung_ID
                WHERE bz.id = 2) as subsel
            JOIN b_regionalverband as rv on rv.id = Beitragsstelle
            WHERE Beitragsjahr = $curyear AND FIND_IN_SET(Beitragsstelle, berechtigte_elemente($uid, 'verband')) > 0;",

    "spaltenbreiten" => array(
        "Erfasst_am"              => "200",
        "Mitglied"                => "450",
        "Beitragsjahr"            => "120",
        "Zahlungspflichtige_BSG"  => "400",
        "Kategorie"               => "150",
        "Zuordnung"               => "150",
        "€"                       => "100",
        "Beitragsstelle"          => "250"
        )
);

$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_meldeliste",
    "auswahltext" => "$bericht Meldelisten",
    "writeaccess" => false,
    "import" => false,
    "query" => "SELECT 
            subsel.id, 
            DATE_FORMAT(Timestamp, '%d.%m.%Y') AS Erfasst_am, 
            Beitragsjahr,
            Mitglied, 
            BSG as Zahlungspflichtige_BSG, 
            Kategorie, 
            Zuordnung,
            Betrag as €,
            rv.Kurzname as Beitragsstelle
            FROM  
            (SELECT ml.id as id, Timestamp, ml.Beitragsjahr, ml.Mitglied, ml.BSG, bz.Zweck as Kategorie, rv.Kurzname as Zuordnung, ml.Beitragsjahr as jahr, ml.Beitragsstelle as Beitragsstelle, Betrag
                FROM b_meldeliste as ml
                JOIN b___beitragszuordnungen as bz on ml.Zuordnung = bz.id
                JOIN b_regionalverband as rv on rv.id = ml.Zuordnung_ID
                WHERE bz.id = 1
                
                UNION ALL 
                
                SELECT ml.id as id, Timestamp, ml.Beitragsjahr, ml.Mitglied, ml.BSG, bz.Zweck as Kategorie, sp.Sparte as Zuordnung, ml.Beitragsjahr as jahr, ml.Beitragsstelle as Beitragsstelle, Betrag
                FROM b_meldeliste as ml
                JOIN b___beitragszuordnungen as bz on ml.Zuordnung = bz.id
                JOIN b_sparte as sp on sp.id = ml.Zuordnung_ID
                WHERE bz.id = 2) as subsel
            JOIN b_regionalverband as rv on rv.id = Beitragsstelle
            WHERE FIND_IN_SET(Beitragsstelle, berechtigte_elemente($uid, 'verband')) > 0;",

    "spaltenbreiten" => array(
        "Erfasst_am"              => "200",
        "Mitglied"                => "450",
        "Beitragsjahr"            => "120",
        "Zahlungspflichtige_BSG"  => "400",
        "Kategorie"               => "150",
        "Zuordnung"               => "150",
        "€"                       => "100",
        "Beitragsstelle"          => "250"
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

