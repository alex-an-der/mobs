<?php


# Mitglieder in der BSG


######################################################################################################

# Mitglieder in der Stamm-BSG
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_mitglieder",
    "auswahltext" => "Mitglieder nach Stamm-BSG",
    "writeaccess" => true,
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
    )
);

# Alle Mitglieder
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_mitglieder",
    "auswahltext" => "Mitglieder nach BSG (lesend)",
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
    "query" => "SELECT mis.id as id, mis.Sparte as Sparte, mis.Mitglied as Mitglied
                from b_mitglieder_in_sparten as mis 
                WHERE FIND_IN_SET(mis.Mitglied, berechtigte_elemente($uid, 'mitglied')) > 0 OR mis.Sparte IS NULL
                order by mis.id desc;
    ",
    "referenzqueries" => array(
        "Sparte" => "SELECT s.id as id, concat (s.Sparte, ' (',v.Kurzname,')') as anzeige
                    from b_sparte as s
                    join b_regionalverband as v on s.Verband = v.id
                    WHERE FIND_IN_SET(s.id, berechtigte_elemente($uid, 'sparte')) > 0
                    ORDER BY anzeige;
        ",
        "Mitglied" => "SELECT mis.mitglied as id , CONCAT(m.Nachname, ', ', m.Vorname, ' (', b.BSG,')') as anzeige 
                        from v_mitglieder_in_bsg_gesamt as mis
                        join b_mitglieder as m on m.id = mis.mitglied
                        join b_bsg as b on mis.BSG = b.id
                        WHERE FIND_IN_SET(mis.mitglied, berechtigte_elemente($uid, 'mitglied')) > 0
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