<?php

$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_mitglieder",
    "auswahltext" => "Stamm-Mitglieder ohne Zugang eintragen",
    "hinweis" => "Hier sind nur Mitglieder aufgelistet, die noch keinen eigenen Zugang zum System haben. Ein manuelles Anlegen sollte die Ausnahme sein. In diesem Fall
     ist der <b>Eintragende</b> und nicht das Mitglied <b>verantwortlich</b> dafür, dass das Mitglied der Datenschutzerklärung und der Mitgliedschaft zugestimmt hat. Der normale Weg sollte immer sein, 
     dass sich das Mitglied selbst registriert.",
    "writeaccess" => true,
    "import" => true,
    "query" => "SELECT m.id as id, Vorname, Nachname, BSG, Mail, m.Geschlecht, m.Geburtsdatum, aktiv
                from b_mitglieder as m
                WHERE 
                    (FIND_IN_SET(BSG, berechtigte_elemente($uid, 'BSG')) > 0 or 
                    ( BSG IS NULL AND FIND_IN_SET(m.id, berechtigte_elemente($uid, 'individuelle_mitglieder')) > 0))
                and m.BSG IS NOT NULL
                and y_id IS NULL
                order by BSG, Vorname desc;
    ",
    "referenzqueries" => array(
        "BSG" => "SELECT b.id, b.BSG as anzeige
        from b_bsg as b
        WHERE FIND_IN_SET(b.id, berechtigte_elemente($uid, 'BSG')) > 0
        ORDER BY anzeige;
        ",
        "Geschlecht" => "SELECT id, auswahl as anzeige
                        from b___geschlecht;
        ",
        "aktiv" => "SELECT id, wert as anzeige
                        from b___an_aus;
        "
    ),
    "spaltenbreiten" => array(
        "y_id"                      => "50", 
        "BSG"                       => "300",
        "Vorname"                   => "150",
        "Nachname"                  => "150",
        "Mail"                      => "250",
        "Geschlecht"                => "100",
        "Geburtsdatum"              => "100",
        "aktiv"                     => "100"
    )
);

# Mitgliederkonten zusammenführen
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_mitglieder",
    "auswahltext" => "Mitgliederkonten zusammenführen",
    "hinweis" => "<p>Wenn ein Mitglied vom BSG-Verwalter erstellt (bzw. importiert wird), ist dieses Konto nicht mit einem LogIn-Konto verknüpft. 
    Wird später vom Mitglied ein LogIn-Konto erstellt (erkennbar an einer y_id), so müssen diese Konten nachträglich verknüpft werden. Nach der Verknüpfung gibt es nur noch ein Konto, 
    mit folgenden Daten:</p>
    <p>Vom Konto, dass das Mitglied mit der Registrierung anlegt (y_id vorhanden):
    <ul>
    <li>Mailadresse</li>
    <li>Passwort</li>
    </ul></p>
    <p>Vom manuell erstellten Konto (keine y_id):
    <ul>
    <li>Stamm-BSG</li>
    <li>Sparten</li>
    <li>aktiv/passiv</li>
    <li>etc.</li>
    </ul></p>
    <p>Dazu muss die y_id des LogIn-Kontos in das y_id-Feld im zugehörigen 
    manuell angelegten Konto eingetragen werden. Um Fehleingaben einzuschränken, müssen die Geburtsdaten der beiden zu verknüpfenden Datensätze übereinstimmen. 
    Wenn dies nicht erfüllt ist oder eine ungültige Nummer eingetragen wird, wird ein Fehler zurückgegeben.</p><p><b>Bitte diese Zusammenführung mit Vorsicht und Bedacht ausführen.</b></p>
    <p>Es werden hier nur Datensätze angezeigt, die die Grundbedingung erfüllen, dass die Geburtsdaten übereinstimmen. Sollte die aufgrund eines Tippfehlers 
    nicht der Fall sein, muss dieser Fehler zunächst behoben werden (z.B. durch die Bearbeitung in den Stammdaten).</p>",
    "writeaccess" => true,
    "import" => false,
    "query" => "SELECT 
                    m.id as id, 
                    m.y_id as 'ajax:y_id', 
                    concat(Vorname, ' ', Nachname) as info:Name,  
                    DATE_FORMAT(m.Geburtsdatum, '%d.%m.%Y') as info:Geburtsdatum
                FROM b_mitglieder as m
                WHERE 
                    (FIND_IN_SET(BSG, berechtigte_elemente($uid, 'BSG')) > 0 or 
                    ( BSG IS NULL AND FIND_IN_SET(m.id, berechtigte_elemente($uid, 'individuelle_mitglieder')) > 0))
                AND m.BSG IS NOT NULL
                AND m.Geburtsdatum IN (
                    SELECT Geburtsdatum
                    FROM b_mitglieder
                    GROUP BY Geburtsdatum
                    HAVING COUNT(*) >= 2
                )
                ORDER BY BSG, Vorname DESC;
    ",
    "ajaxfile" => "ajax_kontenzusammenfuehrung.php",
    "referenzqueries" => array(
        "BSG" => "SELECT b.id, b.BSG as anzeige
        from b_bsg as b
        WHERE FIND_IN_SET(b.id, berechtigte_elemente($uid, 'BSG')) > 0
        ORDER BY anzeige;
        ",
        "Geschlecht" => "SELECT id, auswahl as anzeige
                        from b___geschlecht;
        ",
        "aktiv" => "SELECT id, wert as anzeige
                        from b___an_aus;
        "
    ),
    "spaltenbreiten" => array(
        "ajax:y_id"                 => "50", 
        "info:Name"                 => "300",
        "info:Geburtsdatum"         => "150"
    )
);

?>