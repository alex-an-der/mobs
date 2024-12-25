<?php 
# Datenbank-Zugangsdaten
define("DB_NAME", "db_441127_12");
define("DB_HOST", "x96.lima-db.de");
define("DB_USER", "USER441127");
define("DB_PASS", "BallBierBertha42");

# Anzeige bei Spalten, die über Fremdschlüssel verknüpft sind
define("spartenleitung1_funktionaer","##Vorname## ##Nachname## (##eMail##)");
define("spartenleitung2_funktionaer","Kurz gesagt: ##Vorname##");

# Welche Tabellen sollen nicht angezeigt werden?
define("NOSHOWS", ["log", "yuser"]);

?>