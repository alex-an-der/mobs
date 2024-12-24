<?php
require_once(__DIR__ . "/../inc/include.php");

$createTable = $db->query("SHOW CREATE TABLE sparten;");

$createTable = $createTable[0]['Create Table'];
// Array für Fremdschlüssel
$foreignKeys = [];

// Regex zur Erkennung von Fremdschlüsseln mit FK-Name
$pattern = "/CONSTRAINT `([^`]+)` FOREIGN KEY \\(`([^`]+)`\\) REFERENCES `([^`]+)` \\(`([^`]+)`\\)/";

// Suche nach allen Fremdschlüsseldefinitionen
if (preg_match_all($pattern, $createTable, $matches, PREG_SET_ORDER)) {
    foreach ($matches as $match) {
        $foreignKeys[$match[2]] = [
            'tabelle' => $match[3],
            'spalte' => $match[4],
            'FKname'  => $match[1]  // FK-Name hinzufügen
        ];
    }
}

// Ausgabe des Arrays mit var_dump
show($foreignKeys);
?>
