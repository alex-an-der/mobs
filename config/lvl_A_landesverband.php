<?php


# Regionalverbände
# Rechteverwaltung: Regionalverbände
# Log (zur Entwicklung)
# DEV-Mitglieder in den Sparten


######################################################################################################

# Regionalverbände
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_regionalverband",
    "auswahltext" => "Regionalverbände",
    "writeaccess" => true,
    "query" => "select id, Verband, Kurzname, Internetadresse from b_regionalverband order by id desc;",
    "spaltenbreiten" => array(
        "Name"              => "350",
        "Kurzname"          => "250",
    )
);

# Alle Verbände werden angezeigt
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_regionalverband_rechte",
    "auswahltext" => "Rechteverwaltung: Regionalverbände",
    "writeaccess" => true,
    "hinweis" => "Berechtigt angemeldete Nutzer, Sparten und BSG eines Verbandes zu sehen und zu bearbeiten.",
    "query" => "SELECT r.id as id, r.Verband as Verband, r.Nutzer
                FROM b_regionalverband_rechte as r 
                order by id desc;
                ",
    "referenzqueries" => array(
        "Verband" => "SELECT id, Verband as anzeige from b_regionalverband ORDER BY anzeige;",
        "Nutzer" => "SELECT id, mail as anzeige from y_user ORDER BY anzeige;"
    )
);


$anzuzeigendeDaten['log'] = array(
    "tabellenname" => "log",
    "auswahltext" => "Log (zur Entwicklung)",
    "query" => "SELECT  id, id as Nr, zeit as Timestamp, eintrag as Log from log order by zeit desc;",
    "spaltenbreiten" => array(
        "Nr"        => "80",
        "Timestamp" => "220",
        "Log"       => "1620"
    )
);# Sparten im Regionalverband




$anzuzeigendeDaten['dev'] = array(
    "tabellenname" => "_b_dev_mitglieder_in_sparten",
    "auswahltext" => "DEV-Mitglieder in den Sparten",
    "writeaccess" => true,
    "query" => "SELECT mis.id as id, mis.Sparte as Sparte, mis.Mitglied as Mitglied
                from _b_dev_mitglieder_in_sparten as mis
                left join v_verbands_berechtigte_sparte as vbs on vbs.Sparte = mis.Sparte
                where vbs.Verbandsberechtigter = $uid or mis.Sparte is NULL 
                order by mis.id desc;
    ",# Sparten im Regionalverband

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


?>