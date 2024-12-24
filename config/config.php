<?php 
# Datenbank-Zugangsdaten
define("DB_NAME", "db_441127_12");
define("DB_HOST", "x96.lima-db.de");
define("DB_USER", "USER441127");
define("DB_PASS", "BallBierBertha42");


define("spartenleitung1_funktionaer","##Vorname## ##Nachname## (##eMail##)");
define("spartenleitung2_funktionaer","Kurz gesagt: ##Vorname##");
# Anzeige bei Spalten, die über Fremdschlüssel verknüpft sind
# 1. Spalte ist immer die ID. Wieviele Spalten danach sollen im verknüpften Feld angezeigt werden?
# Beispiel: Die Spalten dind ID | Vorname | Nachname | Ort | PLZ | Mail.
# FK_COLUMNS = 2: Hans Meier
# FK_COLUMNS = 3: Hans Meier Hamburg
# define("FK_COLUMNS", 2);

?>