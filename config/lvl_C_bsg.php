<?php


# Mitglieder in der BSG


######################################################################################################

# Mitglieder in der BSG
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_mitglieder",
    "auswahltext" => "Mitglieder in der BSG",
    "writeaccess" => true,
    "query" => "SELECT m.id as id, m.BSG, m.Vorname, m.Nachname, m.Mail
            from b_mitglieder as m
           -- WHERE FIND_IN_SET(m.id, berechtigte_elemente($uid, 'mitglied')) > 0
             LEFT JOIN b_bsg_rechte as r on r.BSG = m.BSG
             WHERE m.BSG  IS NULL OR Nutzer = $uid
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

# Mitglieder in den Sparten (BSG-Ebene)
/*
SELECT *
from b_mitglieder_in_sparten as mis
join b_mitglieder as m on m.id = mis.Mitglied
join b_bsg as b on b.BSG = m.BSG

join b_bsg_rechte as r on r.;
*/

# Mitglieder in den Sparten 
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_mitglieder_in_sparten",
    "auswahltext" => "Mitglieder in den Sparten",
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
        "Mitglied" => "SELECT mis.id as id , CONCAT(m.Nachname, ', ', m.Vorname, ' (', b.BSG,')') as anzeige 
                        from v_mitglieder_in_bsg_gesamt as mis
                        join b_mitglieder as m on m.id = mis.id
                        join b_bsg as b on mis.BSG = b.id
                        WHERE FIND_IN_SET(mis.id, berechtigte_elemente($uid, 'mitglied')) > 0
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