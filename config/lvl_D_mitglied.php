<?php


# Meine Mitglieder-Daten


######################################################################################################

# Meine Mitglieder-Daten
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_mitglieder",
    "auswahltext" => "Meine Daten",
    "writeaccess" => true,
    "import" => false,
    "query" => "SELECT m.*
            FROM b_mitglieder as m 
            join y_user as y on y.mail = m.Mail
            WHERE y.id = $uid
            order by m.id desc;
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