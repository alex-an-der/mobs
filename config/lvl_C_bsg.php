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
            LEFT JOIN b_bsg_rechte as r on r.BSG = m.BSG
            WHERE m.BSG  IS NULL OR Nutzer = $uid
            order by id desc;
    ",
    "referenzqueries" => array(
        "BSG" => "SELECT b.id, b.BSG as anzeige
        from b_bsg as b
        join b_bsg_rechte as r on r.BSG = b.id 
        where r.Nutzer = $uid 
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