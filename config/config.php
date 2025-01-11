<?php 
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

$uid=10;
$uid=4;


# Sparten (Verbandsansicht)
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_sparte",
    "auswahltext" => "Sparten (Verbandsansicht)",
    "query" => "SELECT s.id, s.Verband as Verband, s.Sparte
        FROM b_sparte as s
        LEFT JOIN b_regionalverband_rechte as r on r.Verband = s.Verband
        WHERE r.Nutzer IS NULL OR r.Nutzer = $uid;
    ",
    "referenzqueries" => array(
        "Verband" => "SELECT v.id, v.Verband as anzeige
        FROM b_regionalverband as v
        JOIN b_regionalverband_rechte as r on r.Verband = v.id 
        where r.Nutzer = $uid 
        ORDER BY anzeige;
        "
    )
);

# BSG (Verbandsansicht)
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_bsg",
    "auswahltext" => "BSG (Verbandsansicht)",
    "hinweis" => "<b>RE </b> = Rechnungsempfänger. In diese Spalten bitte eintragen, wohin eventuelle Rechnungen geschickt werden sollen.",
    "query" => "SELECT b.id,
        b.Verband as Verband,
        BSG,
        Ansprechpartner,
        RE_Name,
        RE_Name2,
        RE_Strasse_Nr,
        RE_Strasse2,
        RE_PLZ_Ort
        FROM b_bsg as b
        LEFT JOIN b_regionalverband_rechte as r on r.Verband = b.Verband
        WHERE Nutzer IS NULL OR Nutzer = $uid;
    ",
    "referenzqueries" => array(
        "Verband" => "SELECT v.id, v.Verband as anzeige
        from b_regionalverband as v
        join b_regionalverband_rechte as r on r.Verband = v.id 
        where r.Nutzer = $uid 
        ORDER BY anzeige;
        "
    ),
    "spaltenbreiten" => array(
        "Verband"                       => "400",
        "BSG"                           => "200",  
        "Ansprechpartner"               => "400",  
        "RE_Name"                       => "200",  
        "RE_Name2"                      => "200",  
        "RE_Strase_Nr"                  => "1200",  
        "RE_Strasse2"                   => "200",  
        "RE_PLZ_Ort"                    => "200"
    )
);

###################################################################################


# Alle Verbände werden angezeigt
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_regionalverband_rechte",
    "auswahltext" => "Rechteverwaltung: Regionalverbände",
    "query" => "SELECT r.id as id, r.Verband as Verband, r.Nutzer
    FROM b_regionalverband_rechte as r 
    order by id desc;
    ",
    "referenzqueries" => array(
        "Verband" => "SELECT id, Verband as anzeige from b_regionalverband ORDER BY anzeige;",
        "Nutzer" => "SELECT id, mail as anzeige from y_user ORDER BY anzeige;"
    )
);


## Ich sehe nur BSG von Verbänden, zu deren Ansicht ich berechtigt bin
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_bsg_rechte",
    "auswahltext" => "Rechteverwaltung: BSG",
    "query" => "SELECT br.id as id, b.id as BSG, br.Nutzer as Nutzer
    from b_regionalverband_rechte as vr
    join b_bsg as b on b.Verband = vr.Verband
    join b_bsg_rechte as br on br.BSG = b.id
    WHERE vr.Nutzer = $uid
    ",
    "referenzqueries" => array(
        "BSG" => "SELECT id, BSG as anzeige from b_bsg ORDER BY anzeige;",
        "Nutzer" => "SELECT id, mail as anzeige from y_user ORDER BY anzeige;"
    )
);

###################################################################################

$anzuzeigendeDaten[] = array(
    "tabellenname" => "log",
    "auswahltext" => "Log (zur Entwicklung)",
    "query" => "SELECT id, id as Nr, zeit as Timestamp, eintrag as Log from log order by zeit desc;",
    "spaltenbreiten" => array(
        "Nr"       => "80",
        "Timestamp" => "220",
        "eintrag" => "1620"
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