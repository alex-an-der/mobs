<?php
$LOCAL_DB_HOST = 0;


require_once(__DIR__ . "/../inc/classes/datenbank.php");

# Datenbank-Zugangsdaten
define("DB_NAME", "db_441127_14");
define("DB_USER", "USER441127");
if($LOCAL_DB_HOST){
    define("DB_HOST", "localhost");
}else{
    define("DB_HOST", "x96.lima-db.de");
}

define("DB_PASS", "BallBierBertha42");
define("TITEL", "LBSV Nds. Mitgliederverwaltung");
# Wie sollen NULL-Werte (=keine Zuordnung) dargestellt werden?
define("NULL_WERT", "---");


# DB direkt hier einbinden
$db = new Datenbank();
?>