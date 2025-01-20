<?php
@require_once(__DIR__ . "/mods/all.head.php");
@require_once(__DIR__ . "/mods/config.head.php");

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


# Sparten (Verbandsansicht)
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_sparte",
    "auswahltext" => "Sparten (Verbandsansicht)",
    "query" => "SELECT s.id, s.Verband as Verband, s.Sparte, s.Sportart as Sportart
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
        ",
        "Sportart" => "SELECT id, CONCAT (Sportart,' (',Sportart_Nr,')') as anzeige from b___sportart ORDER BY anzeige;"
    )
);

# BSG (Verbandsansicht)
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_bsg",
    "auswahltext" => "BSG (Verbandsansicht)",
    "hinweis" => "<b>RE </b> = Rechnungsempfänger. In diese Spalten bitte eintragen, wohin eventuelle Rechnungen geschickt werden sollen.",
    "query" => "SELECT s.id as id, s.Verband as Verband, s.Sparte as Sparte
        FROM b_sparte as s
        LEFT JOIN b_regionalverband_rechte as r on r.Verband = s.Verband
        WHERE s.Verband  IS NULL OR Nutzer = $uid;
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
        "RE_Strasse_Nr"                 => "200",  
        "RE_Strasse2"                   => "200",  
        "RE_PLZ_Ort"                    => "200"
    )
);

###################################################################################
##   RECHTEVERWALTUNG                                                            ##
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


# BSG-Rechte - Wer darf die Mitglieder welcher BSG editieren? 
# Ich sehe nur BSG von Verbänden, zu deren Ansicht ich berechtigt bin
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_bsg_rechte",
    "auswahltext" => "Rechteverwaltung: BSG",
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
    "auswahltext" => "xxLog (zur Entwicklung)",
    "query" => "SELECT 'RO', id, id as Nr, zeit as Timestamp, eintrag as Log from log order by zeit desc;",
    "spaltenbreiten" => array(
        "Nr"       => "80",
        "Timestamp" => "220",
        "Log" => "1620"
    )
);




?>