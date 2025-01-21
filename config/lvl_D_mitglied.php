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
    ),
    "spaltenbreiten" => array(
        "BSG"                       => "600",
        "Vorname"                   => "200",
        "Nachname"                  => "200",
        "Mail"                      => "200"
    )  
);

# Meine Sparten
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_mitglieder_in_sparten",
    "auswahltext" => "Meine Sparten",
    "hinweis" => "An- und Abmeldung zu Sparten bitte über deine Betriebssportgemeinschaft vornehmen.",
    "writeaccess" => false,
    "import" => false,
    "query" => "select y.id, s.Sparte as Sparte, b.BSG as BSG
            from b_mitglieder_in_sparten as mis
            join b_sparte as s on mis.Sparte = s.id
            join b_mitglieder as m on m.id = mis.Mitglied
            join b_bsg as b on b.id = m.BSG 
            join y_user as y on y.mail = m.Mail
            WHERE y.id = $uid;
    "
);


?>