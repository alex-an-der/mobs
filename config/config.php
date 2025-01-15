<?php 
/*
Ich möchte, dass du Änderungen im Codes zunächst mit mir besprichst jetzt. Jetzt geht es erst einmal um ein Konzept. Ich möchte nicht, dass der Nutzer etwas auswählen muss. Ich möchte hingegen lieber so vorgehen. Es gibt in der Datei config. Im Index Referenzcarreries stehen Abfragen an die Datenbank für die betroffene Spalte. Ich möchte den Input zu gestalten, dass der Nutzer. Die Daten wo? Wie gewohnt als Textpfeil in eine Textarea. Einfügt. Das Programm soll im. Einer Referenz. Spalte. Sehen. Ob es passende Datensätze findet? Mithilfe des Selekts im Index Referenzquare ist. Die auf die eingegebenen Schlagworte. Passen. Ich gebe dir ein Beispiel.

$anzuzeigendeDaten["referenzqueries"] = "SELECT ...." ergibt die Datensätze id, Vorname, Namname, Strasse 11, Hans, Christ, Hallerweg 12, Markus, Kummer, Jooballee 13, Christian, Meyer, Hubertusweg

Eine Eingabe von "Chris Haller" liefert die id 11 "Markus" liefert 12 "Chris" liefert 11 und 13

Sollte pro Zeile mehrere. Datensätze identifiziert werden, die passen könnten, soll das, was Importfehler angezeigt werden.

Verstehst du, was ich meine? Hast du Fragen?
*/
require_once(__DIR__ . "/../mods/all.head.php");
require_once(__DIR__ . "/../mods/config.head.php");
# Datenbank-Zugangsdaten
define("DB_NAME", "db_441127_14");
define("DB_HOST", "x96.lima-db.de");
define("DB_USER", "USER441127_bsadm");
define("DB_PASS", "BallBierBertha42");
define("TITEL", "LBSV Nds. Mitgliederverwaltung");

$uid=0;
if (isset($_SESSION['uid'])) $uid = $_SESSION['uid'];

$anzuzeigendeDaten = array();

# tabellenname => Nur hierein kann in dieser Ansicht ein insert oder update ausgeführt werden.
#              => Basistabelle für Referenzierung in anderen Tabellen
# query        => Es muss eine Spalte mit dem Namen "id" angefordert werden, die als eindeutiger Schlüssel verwendet wird.
#              => Die Spalte "id" wird nicht angezeigt. 
#              => Soll die ID des Datensatzes angezeigt werden, muss diese ein zweites Mal angefordert werden (z.B. SELECT id, id as LfdNr. from ...)
#              => Es können nur Spalten bearbeitet werden, die nicht mit einem Alias angefordert werden. Beispiel: SELECT Nachname, vName as Vorname -> nur Nachname kann bearbeitet werden.
#
# KOMPLETTBEISPIEL:
# ----------------
#
/*
$anzuzeigendeDaten[] = array(
    "tabellenname" => "mitglieder",
    "auswahltext" => "Mitglieder Stammdaten",
    "query" => "select * from mitglieder order by id desc;",
    "referenzqueries" => array(
        "Geschlecht" => "select id, geschlecht as anzeige from geschlechter order by geschlecht desc;",
        "Unternehmen" => "SELECT id, CONCAT(Name, ', ', Stadt) as anzeige from unternehmen order by Name;"
    ),
    "spaltenbreiten" => array(
        "Vorname"       => "120px",
        "Nachname"      => "120px",
        "Straße"        => "200px",
        "PLZ"           => "120px",
        "Wohnort"       => "200px",
        "Geschlecht"    => "40px"
    )
);

Kovention: Spalten, die nur lesend angezeigt werden, wird zur Visualisierung für den Nutzer ein Unterstrich vorangestellt. Beispiel: _Verband
*/



# Verbände - Berechtigt: Administratoren
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_regionalverband",
    "auswahltext" => "Regionalverbände",
    "query" => "select id, Verband, Kurzname, Internetadresse from b_regionalverband order by id desc;",
    "spaltenbreiten" => array(
        "Name"              => "350",
        "Kurzname"          => "250",
    )
);


$uid=1  ;


# Sparten im Regionalverband
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_sparte",
    "auswahltext" => "Sparten im Regionalverband",
    "query" => "SELECT s.id as id, s.Verband as Verband, s.Sparte, s.Sportart as Sportart
        FROM b_sparte as s
        LEFT JOIN b_regionalverband_rechte as r on r.Verband = s.Verband
        WHERE r.Nutzer IS NULL OR r.Nutzer = $uid
        order by id desc;
    ",
    "referenzqueries" => array(
        "Verband" => "SELECT v.id, v.Verband as anzeige
        FROM b_regionalverband as v
        JOIN b_regionalverband_rechte as r on r.Verband = v.id 
        where r.Nutzer = $uid 
        ORDER BY anzeige;
        ",
        "Sportart" => "SELECT id, CONCAT (Sportart,' (',Sportart_Nr,')') as anzeige from b___sportart ORDER BY anzeige;"
    )
);

# BSG im Regionalverband
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_bsg",
    "auswahltext" => "BSG im Regionalverband",
    "hinweis" => "<b>RE </b> = Rechnungsempfänger. In diese Spalten bitte eintragen, wohin eventuelle Rechnungen geschickt werden sollen.",
    "query" => "SELECT 
        b.id as id,
        b.Verband as Verband,
        b.BSG as BSG,
        Ansprechpartner,
        RE_Name,
        RE_Name2,
        RE_Strasse_Nr,
        RE_Strasse2,
        RE_PLZ_Ort
        FROM b_bsg as b
        LEFT JOIN b_regionalverband_rechte as r on r.Verband = b.Verband
        WHERE b.Verband  IS NULL OR Nutzer = $uid
        order by id desc;
    ",
    "referenzqueries" => array(
        "Verband" => "SELECT v.id, v.Verband as anzeige
        from b_regionalverband as v
        join b_regionalverband_rechte as r on r.Verband = v.id 
        where r.Nutzer = $uid 
        ORDER BY anzeige;
        ",
        "Ansprechpartner" => "SELECT m.id, CONCAT(Nachname, ', ', Vorname) as anzeige 
                                from b_mitglieder as m
                                join b_bsg as b on b.id=m.BSG
                                join b_regionalverband_rechte as vr on b.Verband = vr.Verband
                                where vr.Nutzer = $uid
                                order by anzeige;"
    ),
    "spaltenbreiten" => array(
        "Verband"                       => "200",
        "BSG"                           => "200",  
        "Ansprechpartner"               => "400",  
        "RE_Name"                       => "200",  
        "RE_Name2"                      => "200",  
        "RE_Strasse_Nr"                 => "200",  
        "RE_Strasse2"                   => "200",  
        "RE_PLZ_Ort"                    => "200"
    ) 
);

# Mitglieder in der BSG
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_mitglieder",
    "auswahltext" => "Mitglieder in der BSG",
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

# Meine Mitglieder-Daten
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_mitglieder",
    "auswahltext" => "Meine Daten",
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
# Mitglieder in den Sparten 
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_mitglieder_in_sparten",
    "auswahltext" => "Mitglieder in den Sparten",
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
    )
);


###################################################################################
##   RECHTEVERWALTUNG                                                            ##
###################################################################################

# Alle Verbände werden angezeigt
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_regionalverband_rechte",
    "auswahltext" => "Rechteverwaltung: Regionalverbände",
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


# BSG-Rechte - Wer darf die Mitglieder welcher BSG editieren? 
# Ich sehe nur BSG von Verbänden, zu deren Ansicht ich berechtigt bin
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_bsg_rechte",
    "auswahltext" => "Rechteverwaltung: BSG",
    "hinweis" => "Berechtigt angemeldete Nutzer, Mitglieder einer BSG zu sehen und zu bearbeiten.",
    "query" => "SELECT br.id as id, br.BSG, br.Nutzer
                from b_bsg_rechte as br
                left join v_verbands_berechtigte_bsg as vrb on br.BSG = vrb.BSG
                where vrb.Verbandsberechtigter = $uid OR br.BSG IS NULL;
                ",
    "referenzqueries" => array(
        "BSG" => "SELECT BSG as id, BSG_Name as anzeige
                    FROM v_verbands_berechtigte_bsg as vrb
                    where vrb.Verbandsberechtigter = $uid 
                    ORDER BY anzeige;",
        "Nutzer" => "SELECT id, mail as anzeige from y_user ORDER BY anzeige;"
    )
);
###################################################################################
##   LOG                                                                         ##
###################################################################################

$anzuzeigendeDaten[] = array(
    "tabellenname" => "log",
    "auswahltext" => "Log (zur Entwicklung)",
    "query" => "SELECT id, id as Nr, zeit as Timestamp, eintrag as Log from log order by zeit desc;",
    "spaltenbreiten" => array(
        "Nr"       => "80",
        "Timestamp" => "220",
        "Log" => "1620"
    )
);


/*
$anzuzeigendeDaten[] = array(
    "tabellenname" => "bsv_1_verband_rechte",
    "auswahltext" => "Rechtemanagement: Verbände",
    "query" => "select id, Nutzer, Verband from bsv_1_verband_rechte order by id desc;",
    "referenzqueries" => array(
        "Nutzer" => "select id, mail as anzeige from yuser order by mail;",
        "Verband" => "SELECT id, `Name` as anzeige from bsv_1_verband order by Name;"
    )
);*/
/*
# BSG 
$anzuzeigendeDaten[] = array(
    "tabellenname" => "bsv_2_bsg",
    "auswahltext" => "BSG",
    "query" => "
        select b.id as id, b.Verband as Verband, b.Name as Name, b.Debitor as Debitor
        from bsv_1_verband_rechte as r
        join bsv_2_bsg as b
        on r.Verband = b.Verband
        WHERE r.berechtigter_yuser=$uid;",

    "referenzqueries" => array(

        "Verband" => "
            select v.id as id, v.Name as anzeige 
            from bsv_1_verband_rechte as r
            join bsv_1_verband as v
            on r.Verband = v.id
            WHERE r.berechtigter_yuser=$uid
            ORDER BY anzeige;
            "
        )
    
);

# Sparten
# Alle Sparten, zu denen der User direkt das Leserecht hat +
# Alle Sparten, zu denen der User das Verbands-Leserecht hat +
# Alle Sparten, die keinem Verband zugeordnet sind

$anzuzeigendeDaten[] = array(
    "tabellenname" => "bsv_2_sparte",
    "auswahltext" => "Unsere Sparten",
    "query" => "
        select s.id as id, v.id as Verband, s.Sparte as Sparte, s.Spartenleiter as Spartenleiter 
        from bsv_2_sparte_rechte as r
        join bsv_2_sparte as s  on r.Sparte = s.id
        join bsv_1_verband as v on s.Verband = v.id
        WHERE r.berechtigter_yuser=$uid 

        union

        select s.id as id, v.id as Verband, s.Sparte as Sparte, s.Spartenleiter as Spartenleiter 
        from bsv_1_verband_rechte as r
        join bsv_2_sparte as s on r.Verband = s.Verband
        join bsv_1_verband as v on s.Verband = v.id
        WHERE r.berechtigter_yuser=$uid

        union

        select s.id as id, v.id as Verband, s.Sparte as Sparte, s.Spartenleiter as Spartenleiter 
        from bsv_2_sparte as s 
        left join bsv_1_verband as v on s.Verband = v.id
        WHERE s.Verband IS NULL;
        ;",
        "referenzqueries" => array(
            "Verband" => "
            select v.id as id, v.Name as anzeige 
            FROM bsv_2_sparte_rechte as r
            join bsv_2_sparte as s on r.Sparte = s.id
            join bsv_1_verband as v on v.id = s.Verband
            WHERE r.berechtigter_yuser=$uid
            
            union
            select v.id as id, Name as anzeige 
            FROM bsv_1_verband_rechte as r
            join bsv_1_verband as v on v.id = r.Verband
            WHERE r.berechtigter_yuser=$uid;
            ;"
        )
    );


# Sparten-Rechte
$anzuzeigendeDaten[] = array(
    "tabellenname" => "bsv_2_sparte_rechte",
    "auswahltext" => "Rechtemanagement: Sparten",
    "query" => "
        select r.id as id, yu.mail, s.id as Sparte from bsv_2_sparte_rechte as r
        join bsv_2_sparte as s on s.id = r.Sparte
        join y_user as yu on r.berechtigter_yuser = yu.id
        WHERE r.berechtigter_yuser=4
        union
        select  r.id as id,yu.mail, s.id as Sparte from bsv_1_verband_rechte as r
        join bsv_2_sparte as s on r.Verband = s.Sparte
        join y_user as yu on r.berechtigter_yuser = yu.id
        WHERE r.berechtigter_yuser=4;"/*,

        MACH DIE RECHTEABFRAGE ÜBER EINE PROCEDURE AM ENDE!
    "referenzqueries" => array(
        "Sparte" => "SELECT id, CONCAT(Name, ', ', Stadt) as anzeige from unternehmen order by Name;"
    )
);

#Verband-Rechte
$anzuzeigendeDaten[] = array(
    "tabellenname" => "bsv_1_verband_rechte",
    "auswahltext" => "Rechtemanagement: Verbände",
    "query" => "select id, berechtigter_yuser, Verband from bsv_1_verband_rechte order by id desc;",
    "referenzqueries" => array(
        "berechtigter_yuser" => "select id, mail as anzeige from y_user order by mail;",
        "Verband" => "SELECT id, `Name` as anzeige from bsv_1_verband order by Name;"
    )
);
*/


?>