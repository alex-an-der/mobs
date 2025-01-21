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
?>