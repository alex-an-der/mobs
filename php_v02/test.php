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
        $FKname = $match[1];
        
        $darstellungspattern = constant($FKname);
        if(!$darstellungspattern) db->log(__FILE__.":".__LINE__." - Die benötigte Konstante $FKname zur Darstelliung einer Fremdschlüsselverknüpfung wurde in config.php nicht gesetzt");
        
        $foreignKeys[$match[2]] = [
            'tabelle' => $match[3],
            'spalte' => $match[4],
            'FKname'  => $FKname,
            'darstellungspattern' => constant($match[1]) // definiert in config.php
        ];
    }
}


// Ausgabe des Arrays mit var_dump
show($foreignKeys);
?>
