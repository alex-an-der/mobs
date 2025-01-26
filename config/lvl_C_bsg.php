<?php


# Mitglieder in der BSG


######################################################################################################

# Mitglieder in der Stamm-BSG
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_mitglieder",
    "auswahltext" => "Mitglieder nach Stamm-BSG",
    "writeaccess" => true,
    "import" => false,
    "query" => "SELECT m.id as id, BSG, Vorname, Nachname, Mail
                from b_mitglieder as m
                WHERE FIND_IN_SET(m.id, berechtigte_elemente($uid, 'stammmitglied')) > 0 or BSG IS NULL
                order by id desc;
    ",
    "referenzqueries" => array(
        "BSG" => "SELECT b.id, b.BSG as anzeige
        from b_bsg as b
        WHERE FIND_IN_SET(b.id, berechtigte_elemente($uid, 'BSG')) > 0
        ORDER BY anzeige;
        "
    ),
    "spaltenbreiten" => array(
        "BSG"                       => "300",
        "Vorname"                   => "150",
        "Nachname"                  => "150",
        "Mail"                      => "250"
    )
);

# Alle Mitglieder
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_mitglieder",
    "auswahltext" => "Mitglieder nach BSG (lesend)",
    "hinweis" => "Hier werden alle Mitglieder angezeigt, die in einer BSG sind, auf die du direkt oder über den Verband berechtigt bist.",
    "writeaccess" => false,
    "query" => "SELECT m.id as id, BSG, Vorname, Nachname, Mail
                from b_mitglieder as m
                WHERE FIND_IN_SET(m.id, berechtigte_elemente($uid, 'stammmitglied')) > 0
                order by id desc;
    ",
    "referenzqueries" => array(
        "BSG" => "SELECT b.id, b.BSG as anzeige
        from b_bsg as b
        "
    )
);


# Mitglieder in den Sparten 
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_mitglieder_in_sparten",
    "auswahltext" => "Mitglieder nach Sparten",
    "writeaccess" => true,
    "query" => "SELECT id,  Mitglied, BSG, Sparte
                from b_mitglieder_in_sparten as mis
                WHERE FIND_IN_SET(Mitglied, berechtigte_elemente(15, 'mitglied')) > 0
                OR FIND_IN_SET(Sparte, berechtigte_elemente(15, 'sparte')) > 0
                OR FIND_IN_SET(BSG, berechtigte_elemente(15, 'BSG')) > 0
                OR mis.Sparte IS NULL
                order by mis.id desc;
    ",
    "referenzqueries" => array(
        "Mitglied" => "SELECT m.id as id, concat(m.Vorname,' ', m.Nachname, ' (Stamm: ', b.BSG,')') as anzeige
                        from b_mitglieder as m
                        join b_bsg as b on b.id = m.BSG
                        WHERE
                            FIND_IN_SET(m.id, berechtigte_elemente($uid, 'mitglied')) > 0 AND
                            FIND_IN_SET(m.BSG, berechtigte_elemente($uid, 'BSG')) > 0
                        ORDER BY anzeige;
        ",
        "BSG" => "SELECT b.id as id, concat(b.BSG,' (',v.Kurzname,')') as anzeige
                    from b_bsg as b
                    join b_regionalverband as v on v.id = b.Verband
                    WHERE
                        FIND_IN_SET(b.id, berechtigte_elemente(15, 'BSG')) > 0;
        ",
        "Sparte" => "SELECT s.id as id, concat (v.Kurzname,': ', s.Sparte) as anzeige
                    from b_sparte as s
                    join b_regionalverband as v on s.Verband = v.id
                    ORDER BY anzeige;
        "
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

######################################################################################################

# Statistik: Mitglieder in Sparten
$statistik[] = array(
    "titel" => "Mitglieder in Sparten in meinen BSG (ungetestet)",
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