<?php


# Meine Mitglieder-Daten


######################################################################################################

# Meine Mitglieder-Daten
/*$anzuzeigendeDaten[] = array(
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
        "BSG"                       => "300",
        "Vorname"                   => "200",
        "Nachname"                  => "200",
        "Mail"                      => "250"
    )  
);*/

$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_mitglieder",
    "auswahltext" => "Meine Daten",
    "writeaccess" => true,
    "import" => false,
    "query" => "SELECT m.id, m.Vorname, m.Nachname, m.Mail
            FROM b_mitglieder as m 
            join y_user as y on y.id = m.y_id
            WHERE y.id = $uid
            order by m.id desc;
    ",
    "spaltenbreiten" => array(
        "BSG"                       => "300",
        "Vorname"                   => "200",
        "Nachname"                  => "200",
        "Mail"                      => "250"
    )  
);


# Meine Sparten
/*select * 
from b_mitglieder_in_sparten as mis
join b_mitglieder as m on m.id=mis.Mitglied
join b_bsg as b on b.id=mis.BSG
join 
*/
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_mitglieder_in_sparten",
    "auswahltext" => "ÜBERARBEITEN!! Meine Sparten",
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

# Meine Berechtigungen
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_regionalverband_rechte",
    "auswahltext" => "Meine Berechtigungen",
    "hinweis" => "Um Berechtigungen zu ändern, wende dich bitte an den Vorstand deines Regionalverbands.",
    "writeaccess" => false,
    "import" => false,
    "query" => "SELECT vr.id as id, 'Regionalverband' as Ebene, v.Verband as 'Berechtigt für'
                from b_regionalverband_rechte as vr
                join b_regionalverband v on vr.Verband = v.id
                where vr.Nutzer=$uid
                union

                select br.id as id, 'Betriebssportgemeinschaft' as Ebene, b.BSG  as 'Berechtigt für'
                from b_bsg_rechte as br
                join b_bsg as b on br.BSG=b.id
                where br.Nutzer=$uid;
    "
);


?>