<?php
ob_start();  // Ausgabe-Pufferung starten vor allen includes
require_once(__DIR__ . "/mods/all.head.php");
require_once(__DIR__ . "/mods/ajax.head.php");
require_once(__DIR__ . "/inc/include.php");
ob_clean();  // Löschen aller bisherigen Ausgaben

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

switch($action) {
    case 'update':
        $id = $data['id'];
        //$id = intval($id, 10);
        $field = $data['field'];
        $value = $data['value'];
        $tabelle = $data['tabelle'];

        $query = "UPDATE `$tabelle` SET `$field` = ? WHERE `id` = ?";
        $args = array($value, $id);
        try {
            $result = $db->query($query, $args);
            $response = ["status" => $result ? "success" : "error"];
        } catch (Exception $e) {
            $db->log("Update error: " . $e->getMessage());
            $response = ["status" => "error", "message" => "Fehler beim Update. Stimmt das Datenformat? Für Details siehe log-Tabelle in der Datenbank."];
        }
        ob_end_clean();
        echo json_encode($response);
        break;

    case 'check':
        $id = $data['id'];
        $field = $data['field'];
        $tabelle = $data['tabelle'];

        $query = "SELECT * FROM `$tabelle` WHERE `id` = ?";
        $args = array($id);
        try {
            $result = $db->query($query, $args);
            if ($result && count($result['data']) > 0) {
                $response = ["status" => "success", "row" => $result['data'][0]];
            } else {
                $response = ["status" => "error", "message" => "Keine Zeile gefunden"];
            }
        } catch (Exception $e) {
            $db->log("Check error: " . $e->getMessage());
            $response = ["status" => "error", "message" => "Fehler beim Zeilenprüfen."];
        }
        ob_end_clean();
        echo json_encode($response);
        break;

    case 'insert':
        $tabelle = $data['tabelle'];
        $defaultValues = $data['defaultValues'];

        $fields = implode(", ", array_keys($defaultValues));
        $placeholders = implode(", ", array_fill(0, count($defaultValues), "?"));
        $values = array_values($defaultValues);

        $query = "INSERT INTO `$tabelle` ($fields) VALUES ($placeholders)";
        try {
            $result = $db->query($query, $values);
            if ($result['data']) {
                $response = ["status" => "success"];
            } else {
                $errorInfo = $db->errorInfo();
                $db->log("Insert error: " . json_encode($errorInfo));
                $response = ["status" => "error", "message" => "Fehler beim Einfügen des Datensatzes. Bitte prüfen Sie die log-Tabelle in der Datenbank!"];
            }
        } catch (Exception $e) {
            $db->log("Exception: " . $e->getMessage());
            $response = ["status" => "error", "message" => "Fehler beim Einfügen des Datensatzes. Bitte prüfen Sie die log-Tabelle in der Datenbank!"];
        }
        ob_end_clean();
        echo json_encode($response);
        break;

    case 'insert_default':
        $tabelle = $data['tabelle'];

        // Insert an empty dataset to let the database take the default values
        $query = "INSERT INTO `$tabelle` () VALUES ()";
        try {
            $result = $db->query($query);
            if ($result) {
                $response = ["status" => "success"];
            } else {
                $errorInfo = $db->errorInfo();
                $db->log("Insert error: " . json_encode($errorInfo));
                $response = ["status" => "error", "message" => "Fehler beim Einfügen des Datensatzes. Bitte prüfen Sie die log-Tabelle in der Datenbank!"];
            }
        } catch (Exception $e) {
            $db->log("Exception: " . $e->getMessage());
            $response = ["status" => "error", "message" => "Fehler beim Einfügen des Datensatzes. Bitte prüfen Sie die log-Tabelle in der Datenbank!"];
        }
        ob_end_clean();
        echo json_encode($response);
        break;

    case 'delete':
        $tabelle = $data['tabelle'];
        $ids = $data['ids'];

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $query = "DELETE FROM `$tabelle` WHERE `id` IN ($placeholders)";
        try {
            $result = $db->query($query, $ids);
            $response = ["status" => "success"];
        } catch (Exception $e) {
            $db->log("Delete error: " . $e->getMessage());
            $response = ["status" => "error", "message" => "Fehler beim Löschen der Daten."];
        }
        ob_end_clean();
        echo json_encode($response);
        break;

    case 'check_duplicates':
        $tabelle = $data['tabelle'];

        // Get columns excluding auto-increment columns
        $columnsQuery = "SHOW COLUMNS FROM `$tabelle`";
        $columnsResult = $db->query($columnsQuery);
        $columns = array_filter($columnsResult, function($column) {
            return $column['Extra'] !== 'auto_increment';
        });
        $columns = array_column($columns, 'Field');

        // Build query to find duplicates
        $columnsList = implode(", ", $columns);
        $duplicatesQuery = "
            SELECT id
            FROM (
                SELECT id, COUNT(*) OVER (PARTITION BY $columnsList) AS cnt
                FROM `$tabelle` 
            ) sub
            WHERE cnt > 1 
        ";
        try {
            $duplicatesResult = $db->query($duplicatesQuery);
            $duplicateIds = array_column($duplicatesResult, 'id');
            $response = ["status" => "success", "duplicates" => $duplicateIds];
        } catch (Exception $e) {
            $db->log("Check duplicates error: " . $e->getMessage());
            $response = ["status" => "error", "message" => "Fehler beim Überprüfen auf doppelte Einträge."];
        }
        ob_end_clean();
        echo json_encode($response);
        break;

   

    case 'validate':
        $response = checkDaten($data, $db);
        ob_end_clean();
        echo json_encode($response);
        break;

    case 'import':
        $response = checkDaten($data, $db);
        if($response['status'] == "success"){
            try {
                $db->query("START TRANSACTION");
                
                $successCount = 0;
                $errorCount = 0;
                $errorLog = [];
                $insertQuery = $response['insert_query'];

                foreach($response['args'] as $index => $args) {
                    try {
                        $result = $db->query($insertQuery, $args);
                        if(isset($result['error'])) {
                            $errorCount++;
                            $errorLog[] = ["row" => $index, "data" => $args, "error" => $result['error']];    
                        } else {
                            $successCount++;
                        }
                    } catch (Exception $e) {
                        $errorCount++;
                        $errorLog[] = ["row" => $index, "data" => $args, "error" => $e->getMessage()];
                    }
                }
            
                if($errorCount == 0) {
                    $db->query("COMMIT");
                    $response = ["status" => "success", "message" => "Alle $successCount Datensätze wurden importiert."];
                } else {
                    $db->query("ROLLBACK");
                    $response = [
                        "status" => "error", 
                        "message" => "Fehler beim Import: $errorCount von ".($successCount + $errorCount)." Datensätzen fehlgeschlagen.",
                        "errors" => json_encode($errorLog)
                    ];
                }
            }
            catch (Exception $e) {
                $db->query("ROLLBACK");
                $response = ["status" => "error", "message" => "Schwerwiegender Fehler: " . $e->getMessage()];
            }

            ob_end_clean();
            echo json_encode($response);
            exit;
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Ungültige Aktion']);
        break;
}
function checkDaten($data, $db){
    $importDatenzeilen = $data['rows'];

    //show($importDatenzeilen);

    $importDatensaetze = array();
    $zeilenNummer = 0;
    $FK_Spalten = array();
    foreach($importDatenzeilen as $zeile){
        // Hinterer Trenner sorgt für sauberen Algorhithmus
        $zeile = $zeile.",";
        $currentField = '';
        $inQuotes = false;
        $quoteChar = '';
        $feldNummer = 0;

        for ($i = 0; $i < strlen($zeile); $i++) {
            $char = $zeile[$i];

            // Quote handling, nur für FK-Spalten
            if (in_array($feldNummer,$FK_Spalten) && ($char === '"' || $char === "'") && ($i === 0 || $zeile[$i-1] !== '\\')) {
                if (!$inQuotes) {
                $inQuotes = true;
                $quoteChar = $char;
                } elseif ($char === $quoteChar) {
                    $inQuotes = false;
                }
            }else{
                // separator gefunden
                // Fall 1: FK-Spalte, dann nur nich in Quotes und " " oder ","
                // Fall 2: Nicht FK-Spalte, dann nur bei ",", Quotes egal.
                if(in_array($feldNummer,$FK_Spalten) && !$inQuotes && ($char === ',' || ($char === ' ')) ||
                (!in_array($feldNummer,$FK_Spalten) && $char === ',')){
                 
                    //if (!$inQuotes && ($char === ',' || ($char === ' ' && in_array($feldNummer,$FK_Spalten)))) {
                        // Immer einen neuen Suchbegriff hinzufügen (der jetzige ist abgeschlossen)
                        // Einzige Ausnahme: Leerzeichen hintereinander oder hinter dem Komma.
                        // Nicht FK-Spalten überspringen, die Header aber immer mitnehmen.
                        //&& ($zeilenNummer===0 || in_array($feldNummer,$FK_Spalten)))
                        if(!($currentField === '' && $char===' ')){
                            $importDatensaetze[$zeilenNummer][$feldNummer][] = trim($currentField);
                        }
                        
                        // Bei einem Komme => neues Feld
                        if($char === ','){
                            // Im Header die FK-Spalten identifizieren (Indizes sammeln)
                            if($zeilenNummer === 0 && isset($data['suchQueries'][$currentField])){
                                $FK_Spalten[] = $feldNummer;
                            }
                            $feldNummer++;
                        }
                        $currentField = '';
                    }else{
                        // Weiter lesen
                        $currentField .= $char;
                    }
            }
        }
        $zeilenNummer++;
    }


#####################################################

    $suchQueries = $data['suchQueries'];
    $tabelle = $data['tabelle'];
    $suchStrings = [];
    
    foreach ($suchQueries as $index => $query) { 
        $result = $db->query($query);
        foreach($result as $row){
            foreach ($row as $item) {
                $id = $item['id'] ?? $item['ID'] ?? null;
                if ($id === null) {
                    $response = ["status" => "error", "message" => "Der Suchquery, der in der config definiert ist, muss ein Feld 'id' zurückliefern, damit das Suchergebnis zugewiesen werden kann."];
                    ob_end_clean();
                    echo json_encode($response);
                    exit;
                }
                unset($item['id'], $item['ID']);
                $suchStrings[$index][$id] = implode(' ', $item);
            }
        }
    }
        

    $spalten = array();
    $insertQuery = "INSERT INTO `$tabelle` (";
    foreach($importDatensaetze[0] as $spalte)
    {
        // Beim Header gibt es pro Spalte nur einen Wert - das ist 
        // bei den Suchbegriffen anders, daher hier der Wert an [0].

        $insertQuery .= "`$spalte[0]`, ";
        $spalten[] = $spalte[0];
    }
    $insertQuery = rtrim($insertQuery, ", ");
    $insertQuery .= ") VALUES (";
    foreach($importDatensaetze[0] as $spalte)
    {
        $insertQuery .= "?, ";
    }
    $insertQuery = rtrim($insertQuery, ", ");
    $insertQuery .= ")";
    
    unset($importDatensaetze[0]);


    /*
    echo "------------------";
    show($spalten);
    echo "+++++++<br>";
    show($importDatensaetze);
    echo "+++++++<br>";
    show($suchStrings);
    */

    /*
    Sparte,Mitglied,Freitext
    Fuß,Berecht, Hallo Welt
    Fuß, Ditte, Mister 300
    */
    // Nachdem alles gesetzt ist (Was wird wo gesucht), gehe jetzt den Import Zeile für Zeile und Feld für Feld durch
    
    $ERROR_OVER_ALL = false;
    $error_msg = "";
    $alleArgs = array();
    $zeile = 1; // Header-Zeile wird rausgeschnitten, daher beginnen die Daten bei Zeile 2
    //unset($importDatenzeilen);
    // Zeile für Zeile
    foreach($importDatensaetze as $importZeile)
    {
        
        $zeile ++;
        

        //$Datensatz_kann_importiert_werden = true;
        $datenSatzArgs = array();
        
       // Spalte für Spalte
        //foreach($importZeile as $FeldIndex => $importFeld)
        foreach($spalten as $FeldIndex => $spalte)
        {
            $ERROR = false;
            // Ist es eine FK-Spalte?
            if(isset($suchStrings[$spalte]))
            {
                $suchString = $suchStrings[$spalte];
                //Gehe jede einzelne ID durch und schaue, ob das passt
                
                // Gehe jedes Heuhaufen-Feld in aus der Datenbank durch
                foreach($suchString as $id => $suchFeld)
                {
                    //$feldZaehler = 0;
                    // Gehe jeden Import-Suchbegriff (Nadel) durch - alle Nadeln müssen gefunden werden
                    foreach($importDatensaetze[$zeile-1][$FeldIndex] as $importWort){
                        // Wird DIESES Wort im Suchstring (= eine ID) gefunden 
                        if(strpos($suchFeld, $importWort) !== false){
                            if(isset($datenSatzArgs[$FeldIndex]))
                                if($datenSatzArgs[$FeldIndex] != $id){
                                    $ERROR = true;
                                    $ERROR_OVER_ALL = true;
                                    $error_msg .= "<p>Der Import <b>$importFeld</b> in Zeile $zeile ($spalte) liefert kein eindeutiges Ergebnis. Bitte pr&auml;zisieren.</p>";
                                    break;
                                }
                                if(!$ERROR){
                                    $datenSatzArgs[$FeldIndex] = $id;
                                }
                        }else{
                            // Wort nicht gefunden => Diese ID kann es nicht sein
                            // Wurde diese ID schon gesetzt?
                            if(isset($datenSatzArgs[$FeldIndex]))
                                if($datenSatzArgs[$FeldIndex] === $id)
                                    unset($datenSatzArgs[$FeldIndex]);
                            break;
                        }
                    }
                } 
                //$feldZaehler++;
                // Alles durch, aber keine ID konnte zugewiesen werden
                if(!isset($datenSatzArgs[$FeldIndex])){
                    $ERROR = true;
                    $ERROR_OVER_ALL = true;
                    $error_msg .= "<p>In <b>Zeile $zeile ($spalte)</b> konnte kein Datensatz identifiziert werden, da die nicht alle Schl&uuml;sselworte gefunden wurden.</p>";
                    //break;
                }

            }else{ // Keine FK-Spalte (Einfach Inhalt importieren)
                $daten = $importDatensaetze[$zeile-1][$FeldIndex][0];
                $datenSatzArgs[$FeldIndex] = $daten;
            }

   
        }
        $alleArgs[] = $datenSatzArgs;
    }

    if($ERROR_OVER_ALL){
        $response = ["status" => "error", "message" => $error_msg];
    }else{
        $response = ["status" => "success", "insert_query" => $insertQuery, "args" => $alleArgs];
    }
    return $response;
}
?>