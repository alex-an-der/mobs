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