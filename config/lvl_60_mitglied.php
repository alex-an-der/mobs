<?php


# Meine Mitglieder-Daten


######################################################################################################



$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_mitglieder",
    "auswahltext" => "Meine Daten bearbeiten",
    "hinweis" => "Bitte nicht vergessen, unter <b>Wer darf meine Daten sehen</b> deine BSG zu berechtigen, deine Daten zu verwalten. Neue Rechte vergibst du mit <b>einf&uuml;gen</b>.",
    "writeaccess" => true,
    "import" => false,
    "query" => "SELECT m.id, m.Vorname, m.Nachname, m.Mail, m.Geschlecht, m.Geburtsdatum, m.Mailbenachrichtigung
            FROM b_mitglieder as m 
            join y_user as y on y.id = m.y_id
            WHERE y.id = $uid
            order by m.id desc;
    ",
    "referenzqueries" => array(
        "Geschlecht" => "SELECT id, auswahl as anzeige
                        from b___geschlecht;
        ",
        "Mailbenachrichtigung" => "SELECT id, wert as anzeige
                        from b___an_aus;
        "
    ),
    "spaltenbreiten" => array(
        "BSG"                       => "300",
        "Vorname"                   => "200",
        "Nachname"                  => "200",
        "Mail"                      => "250",
        "Geschlecht"                => "120",
        "Geburtsdatum"              => "200",
        "Mailbenachrichtigung"      => "200"
    )  
);

#  Wer darf meine Daten sehen?

$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_individuelle_berechtigungen",
    "auswahltext" => "Meine Daten zur Bearbeitung freigeben.",
    "hinweis" => "Bearbeiter der von dir angegebenen Betriebssportgruppen dürfen deine Daten einsehen und diese verarbeiten. Dies ist notwendig, um dich in einer oder mehreren BSG zu verwalten. Solltest du eine Berechtigung löschen, kann die betreffende BSG deine Daten trotzdem noch so lange sehen wie du dort Mitglied bist ('berechtigtes Interesse' nach DSGVO).  Um ein neues Recht zu vergeben, klicke auf <b>'Einf&uuml;gen'</b>.",
    "writeaccess" => true,
    "import" => true,
    "query" => "SELECT ib.id as id, m.id as Mitglied, ib.BSG as BSG
                from b_individuelle_berechtigungen as ib
                join b_mitglieder as m on ib.Mitglied=m.id
                join b_bsg as b on ib.BSG = b.id 
                WHERE m.y_id = $uid 
                ORDER BY b.BSG asc;
    ",
    "referenzqueries" => array(
        "Mitglied" => "SELECT id, concat(Vorname,' ',Nachname) as anzeige
                        from b_mitglieder WHERE y_id = $uid;
        ",
        "BSG" => "SELECT b.id, concat(b.BSG, ' (',v.Kurzname,')') as anzeige
            from b_bsg as b
            join b_regionalverband as v on b.Verband = v.id
            ORDER BY anzeige asc;
        "
    )
);


# Stamm-BSG
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_mitglieder",
    "auswahltext" => "$bericht Meine Stamm-BSG",
    "hinweis" => "Die Stamm-BSG führt den Basis-Beitrag ab. Um die Stamm-BSG zu wechseln, muss zuerst die alte BSG von der BSG-Verwaltung ausgetragen werden.
    Sparten können auch über andere BSG besucht werden. Bitte im Bedarfsfall vorher abklären, ob das im individuellen Fall möglich ist.",
    "writeaccess" => false,
    "import" => false,
    "query" => "SELECT m.y_id as id, concat (m.Vorname, ' ', m.Nachname) as Name, b.BSG as `Stamm-BSG`
            from b_mitglieder as m
            join b_bsg as b on b.id = m.BSG
            WHERE m.y_id = $uid;
    "
);

$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_mitglieder_in_sparten",
    "auswahltext" => "$bericht In diesen Sparten bin ich angemeldet",
    "hinweis" => "An- und Abmeldung zu Sparten bitte über deine Betriebssportgemeinschaft vornehmen.",
    "writeaccess" => false,
    "import" => false,
    "query" => "SELECT m.y_id as id, s.Sparte as Sparte, b.BSG as BSG
            from b_mitglieder_in_sparten as mis
            join b_sparte as s on mis.Sparte = s.id
            join b_mitglieder as m on m.id = mis.Mitglied
            join b_bsg as b on b.id = mis.BSG 
            WHERE m.y_id = $uid;
    "
);


# Selbst aus y_user löschen
$anzuzeigendeDaten[] = array(
    "tabellenname" => "y_user",
    "auswahltext" => "Lösche meine Daten",
    "hinweis" => "Hier kannst du alle Daten von dir löschen. Das Löschen ist unwiderruflich. Wähle dazu deinen Datensatz aus und klicke auf 'Löschen'.",
    "writeaccess" => false,
    "deleteanyway" => true,
    "import" => false,
    "query" => "select id, mail
        from y_user
        WHERE id = $uid;
    "
);



?>
