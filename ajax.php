<?php
ob_start();
require_once(__DIR__ . "/user_includes/all.head.php");
require_once(__DIR__ . "/user_includes/ajax.head.php");
require_once(__DIR__ . "/inc/include.php");
ob_clean();

// Set error handling
ini_set('display_errors', 0);
error_reporting(E_ERROR | E_PARSE);

try {
    $data = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON decode error: " . json_last_error_msg());
        throw new Exception("Invalid JSON: " . json_last_error_msg());
    }
    
    $action = $data['action'] ?? '';
    $selectedTableID = isset($_GET['tab']) ? $_GET['tab'] : "";
    if (isset($data['selectedTableID'])) {
        $selectedTableID = $data['selectedTableID'];
    }

    switch($action) {
        case 'update':
            $id = $data['id'];
            //$id = intval($id, 10);
            $field = $data['field'];
            
            // Skip update if it's an info column
            if (strpos($field, 'info:') === 0) {
                $response = ["status" => "error", "message" => "Info columns cannot be updated."];
                ob_end_clean();
                echo json_encode($response);
                break;
            }
            
            $value = $data['value'];
            $tabelle = $data['tabelle'];

            $query = "UPDATE `$tabelle` SET `$field` = ? WHERE `id` = ?";
            $args = array($value, $id);
            try {
                $result = $db->query($query, $args);
                $response = ["status" => $result ? "success" : "error"];
            } catch (Exception $e) {
                $db->log("Update error: " . $e->getMessage());
                $response = ["status" => "error", "message" => "Fehler beim Update. Stimmt das Datenformat? Für Details siehe adm_log-Tabelle in der Datenbank."];
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
                    $response = ["status" => "error", "message" => "Fehler beim Einfügen des Datensatzes. Bitte prüfen Sie die adm_log-Tabelle in der Datenbank!"];
                }
            } catch (Exception $e) {
                $db->log("Exception: " . $e->getMessage());
                $response = ["status" => "error", "message" => "Fehler beim Einfügen des Datensatzes. Bitte prüfen Sie die adm_log-Tabelle in der Datenbank!"];
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
                    $response = ["status" => "error", "message" => "Fehler beim Einfügen des Datensatzes. Bitte prüfen Sie die adm_log-Tabelle in der Datenbank!"];
                }
            } catch (Exception $e) {
                $db->log("Exception: " . $e->getMessage());
                $response = ["status" => "error", "message" => "Fehler beim Einfügen des Datensatzes. Bitte prüfen Sie die adm_log-Tabelle in der Datenbank!"];
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
                // Fehlertext aus sys_error_manager holen
                $errMsg = $e->getMessage();
                $sql = "SELECT user_message FROM sys_error_manager WHERE ? LIKE CONCAT('%', raw_message, '%') LIMIT 1";
                $errResult = $db->query($sql, [$errMsg]);
                if ($errResult && isset($errResult['data'][0]['user_message']) && $errResult['data'][0]['user_message']) {
                    $userMsg = $errResult['data'][0]['user_message'];
                    $response = ["status" => "error", "message" => $userMsg];
                } else {
                    $response = ["status" => "error", "message" => "Fehler beim Löschen der Daten." . ($errMsg ? (": " . $errMsg) : "")];
                }
            }
            ob_end_clean();
            echo json_encode($response);
            break;

        case 'check_duplicates':
            $tabelle = $data['tabelle'];

            // Get columns excluding auto-increment columns
            $columnsQuery = "SHOW COLUMNS FROM `$tabelle`";
            $columnsResult = $db->query($columnsQuery)['data'];
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
                $duplicateIds = array();
                if(isset($db->query($duplicatesQuery)['data'])){
                    $duplicatesResult = $db->query($duplicatesQuery)['data'];
                    $duplicateIds = array_column($duplicatesResult, 'id');
                }
                $response = ["status" => "success", "duplicates" => $duplicateIds];
            } catch (Exception $e) {
                $db->log("Check duplicates error: " . $e->getMessage());
                $response = ["status" => "error", "message" => "Fehler beim Überprüfen auf doppelte Einträge."];
            }
            ob_end_clean();
            echo json_encode($response);
            break;

        case 'get_table_structure':
            $tabelle = $data['tabelle'];
            $selectedTableID = $data['selectedTableID'];
            $result = [];

            if (isset($anzuzeigendeDaten[$selectedTableID])) {
                $query = $anzuzeigendeDaten[$selectedTableID]['query'];
                $result['configQuery'] = $query;

                $columns = [];
                $foreignKeys = [];
                $referenzqueries = $anzuzeigendeDaten[$selectedTableID]['referenzqueries'] ?? [];

                // Extrahiere die SELECT-Spalten (inkl. info:-Felder, aber ohne id)
                if (preg_match('/SELECT\s+(.+?)\s+FROM/is', $query, $matches)) {
                    $selectPart = trim($matches[1]);
                    $columnExprList = [];
                    $currentExpr = '';
                    $parenthesesDepth = 0;
                    for ($i = 0; $i < strlen($selectPart); $i++) {
                        $char = $selectPart[$i];
                        if ($char === '(') $parenthesesDepth++;
                        if ($char === ')') $parenthesesDepth--;
                        if ($char === ',' && $parenthesesDepth === 0) {
                            $columnExprList[] = trim($currentExpr);
                            $currentExpr = '';
                        } else {
                            $currentExpr .= $char;
                        }
                    }
                    if (!empty($currentExpr)) {
                        $columnExprList[] = trim($currentExpr);
                    }

                    foreach ($columnExprList as $expr) {
                        // Extrahiere Alias oder Spaltennamen
                        $columnName = null;
                        // AS alias
                        if (preg_match('/\s+AS\s+([`\'"]?)([a-zA-Z0-9_:]+)\1\s*$/i', $expr, $m)) {
                            $columnName = $m[2];
                        }
                        // Kein AS, aber alias (z.B. "foo bar")
                        elseif (preg_match('/\s+([`\'"]?)([a-zA-Z0-9_:]+)\1\s*$/i', $expr, $m)) {
                            $columnName = $m[2];
                        }
                        // table.column
                        elseif (preg_match('/([a-zA-Z0-9_]+)\.([`\'"]?)([a-zA-Z0-9_]+)\2/i', $expr, $m)) {
                            $columnName = $m[3];
                        }
                        // Nur Spaltenname
                        elseif (preg_match('/^([`\'"]?)([a-zA-Z0-9_:]+)\1$/i', $expr, $m)) {
                            $columnName = $m[2];
                        }

                        // id überspringen
                        if ($columnName && strcasecmp($columnName, 'id') === 0) continue;

                        if ($columnName) {
                            $isInfo = (strpos($columnName, 'info:') === 0);
                            $label = $isInfo ? substr($columnName, 5) : $columnName;
                            $col = [
                                'Field' => $columnName,
                                'Label' => $label,
                                'Type' => 'text'
                            ];
                            // Für info:-Felder: Typ immer text, referenzquery verwenden
                            if ($isInfo) {
                                if (isset($referenzqueries[$columnName])) {
                                    try {
                                        $fkResult = $db->query($referenzqueries[$columnName]);
                                        if (isset($fkResult['data'])) {
                                            $foreignKeys[$columnName] = $fkResult['data'];
                                        }
                                    } catch (Exception $e) {
                                        // ignore
                                    }
                                }
                            } else {
                                // Für andere Felder: Typ aus DB
                                try {
                                    $columnTypeQuery = "SHOW COLUMNS FROM `$tabelle` WHERE Field = '$columnName'";
                                    $columnTypeResult = $db->query($columnTypeQuery);
                                    if (isset($columnTypeResult['data'][0]['Type'])) {
                                        $col['Type'] = $columnTypeResult['data'][0]['Type'];
                                    }
                                    if (isset($columnTypeResult['data'][0]['Null'])) {
                                        $col['nullable'] = ($columnTypeResult['data'][0]['Null'] === 'YES');
                                    }
                                } catch (Exception $e) {
                                    // ignore
                                }
                                // referenzquery für dieses Feld? DBI821
                                if (isset($referenzqueries[$columnName])) {
                                    try {
                                        $fkResult = $db->query($referenzqueries[$columnName]);
                                        if (isset($fkResult['data'])) {
                                            $foreignKeys[$columnName] = $fkResult['data'];
                                        }else{ // referenzquery, aber keine Daten
                                            if($col['nullable']){ // Array im Array, also [[...]]
                                                $foreignKeys[$columnName] = [['id' => NULL, 'anzeige' => NULL_WERT]];
                                            }else{
                                                $foreignKeys[$columnName] = [['id' => -1, 'anzeige' => NULL_BUT_NOT_NULLABLE]];
                                            }
                                        }
                                    } catch (Exception $e) {
                                        // ignore
                                    }
                                }
                            }
                            $columns[] = $col;
                        }
                    }
                }

                $result['columns'] = $columns;
                $result['foreignKeys'] = $foreignKeys;
                $result['status'] = 'success';
            } else {
                $result['status'] = 'error';
                $result['message'] = "Invalid table ID: $selectedTableID";
            }

            ob_end_clean();
            echo json_encode($result);
            break;

        /*case 'get_table_structure':
            $tabelle = $data['tabelle'];
            $selectedTableID = $data['selectedTableID'];
            $result = [];

            // Check if table ID exists in configuration
            if (isset($anzuzeigendeDaten[$selectedTableID])) {
                
                // Get the query from configuration
                $query = $anzuzeigendeDaten[$selectedTableID]['query'];
                
                // Store the raw query in result for client-side use
                $result['configQuery'] = $query;
                
                // Extract columns from the SELECT part of the query
                $columns = [];
                try {
                    if (preg_match('/SELECT\s+(.+?)\s+FROM/is', $query, $matches)) {
                        $selectPart = trim($matches[1]);
                        
                        // Split into individual column expressions
                        $columnExprList = [];
                        $currentExpr = '';
                        $parenthesesDepth = 0;
                        
                        // Parse the SELECT clause more carefully to handle functions and subqueries
                        for ($i = 0; $i < strlen($selectPart); $i++) {
                            $char = $selectPart[$i];
                            
                            if ($char === '(') $parenthesesDepth++;
                            if ($char === ')') $parenthesesDepth--;
                            
                            if ($char === ',' && $parenthesesDepth === 0) {
                                $columnExprList[] = trim($currentExpr);
                                $currentExpr = '';
                            } else {
                                $currentExpr .= $char;
                            }
                        }
                        
                        // Add the last expression
                        if (!empty($currentExpr)) {
                            $columnExprList[] = trim($currentExpr);
                        }
                        
                        foreach ($columnExprList as $expr) {
                            // Try different patterns to extract column names/aliases
                            $columnName = null;
                            
                            // Case 1: AS alias
                            if (preg_match('/\s+AS\s+([`\'"]?)([a-zA-Z0-9_]+)\1\s*$/i', $expr, $matches)) {
                                $columnName = $matches[2];
                            }
                            // Case 2: Implicit alias (no AS keyword)
                            elseif (preg_match('/\s+([`\'"]?)([a-zA-Z0-9_]+)\1\s*$/i', $expr, $matches) && !preg_match('/\bFROM\b|\bWHERE\b|\bGROUP\b|\bHAVING\b|\bORDER\b|\bLIMIT\b/i', $matches[0])) {
                                $columnName = $matches[2];
                            }
                            // Case 3: Simple column name with table prefix
                            elseif (preg_match('/([a-zA-Z0-9_]+)\.([`\'"]?)([a-zA-Z0-9_]+)\2/i', $expr, $matches)) {
                                $columnName = $matches[3];
                            }
                            // Case 4: Simple column name
                            elseif (preg_match('/^([`\'"]?)([a-zA-Z0-9_]+)\1$/i', $expr, $matches)) {
                                $columnName = $matches[2];
                            }
                            
                            if ($columnName && strcasecmp($columnName, 'id') !== 0) {
                                $columnInfo = ['Field' => $columnName];
                                
                                // Try to get column type from database
                                try {
                                    $columnTypeQuery = "SHOW COLUMNS FROM `$tabelle` WHERE Field = '$columnName'";
                                    $columnTypeResult = $db->query($columnTypeQuery);
                                    
                                    if (isset($columnTypeResult['data'][0]['Type'])) {
                                        $columnInfo['Type'] = $columnTypeResult['data'][0]['Type'];
                                    } else {
                                        $columnInfo['Type'] = 'text'; // Default type
                                    }
                                } catch (Exception $e) {
                                    $columnInfo['Type'] = 'text'; // Default type
                                }
                                
                                $columns[] = $columnInfo;
                            }
                        }
                    }
                } catch (Exception $e) {
                    // Silent error handling
                }
                
                if (!empty($columns)) {
                    $result['columns'] = $columns;
                } else {
                    // Try executing the query
                    try {
                        $limitedQuery = preg_replace('/;$/', ' LIMIT 0, 1;', $query);
                        $queryResult = $db->query($limitedQuery);
                        
                        if (isset($queryResult['data']) && !empty($queryResult['data'])) {
                            $columns = [];
                            foreach (array_keys($queryResult['data'][0]) as $column) {
                                if (strcasecmp($column, 'id') !== 0) {
                                    $columnInfo = [
                                        'Field' => $column,
                                        'Type' => 'text' // Default type
                                    ];
                                    
                                    // Try to get column type
                                    try {
                                        $columnTypeQuery = "SHOW COLUMNS FROM `$tabelle` WHERE Field = '$column'";
                                        $columnTypeResult = $db->query($columnTypeQuery);
                                        
                                        if (isset($columnTypeResult['data'][0]['Type'])) {
                                            $columnInfo['Type'] = $columnTypeResult['data'][0]['Type'];
                                        }
                                    } catch (Exception $e) {
                                        // Silent error handling
                                    }
                                    
                                    $columns[] = $columnInfo;
                                }
                            }
                            
                            $result['columns'] = $columns;
                        } else {
                            // Create empty column list - not falling back to database schema
                            $result['columns'] = [];
                        }
                    } catch (Exception $e) {
                        // Create empty column list - not falling back to database schema
                        $result['columns'] = [];
                    }
                }
                
                // Get foreign keys from referenzqueries
                $foreignKeys = [];
                if (isset($anzuzeigendeDaten[$selectedTableID]['referenzqueries'])) {
                    $referenzqueries = $anzuzeigendeDaten[$selectedTableID]['referenzqueries'];
                    
                    foreach ($referenzqueries as $column => $query) {
                        try {
                            $fkResult = $db->query($query);
                            
                            if (isset($fkResult['data'])) {
                                $foreignKeys[$column] = $fkResult['data'];
                            }
                        } catch (Exception $e) {
                            // Silent error handling
                        }
                    }
                }
                
                $result['foreignKeys'] = $foreignKeys;
                $result['status'] = 'success';
            } else {
                $result['status'] = 'error';
                $result['message'] = "Invalid table ID: $selectedTableID";
            }
            
            ob_end_clean();
            echo json_encode($result);
            break;*/

        case 'insert_record':
            $tabelle = $data['tabelle'];
            $recordData = (array)$data['data'];
            
            // Entferne "info:"-Präfix aus allen Keys, Werte bleiben erhalten
            $filteredRecordData = [];
            foreach ($recordData as $key => $value) {
                if (strpos($key, 'info:') === 0) {
                    $key = substr($key, 5);
                }
                $filteredRecordData[$key] = $value;
            }
            $fields = array_keys($filteredRecordData);
            $values = array_values($filteredRecordData);

            // Convert "NULL" strings to actual NULL
            $values = array_map(function($val) {
                return $val === "NULL" ? null : $val;
            }, $values);

            $placeholders = array_fill(0, count($fields), '?');

            $sql = "INSERT INTO $tabelle (" . implode(',', $fields) . ") VALUES (" . implode(',', $placeholders) . ")";

            try {
                $result = $db->query($sql, $values);
                if (isset($result['error'])) {
                    ob_end_clean();
                    echo json_encode([
                        'status' => 'error',
                        'error_ID' => $result['error'], 
                        'error_code' => $result['errorcode'], 
                        'message' => $result['message']
                    ]);
                } else {
                    ob_end_clean();
                    echo json_encode([
                        'status' => 'success'
                    ]);
                }
            } catch (Exception $e) {
                $db->log("Insert error: " . $e->getMessage());
                ob_end_clean();
                echo json_encode([
                    'status' => 'error',
                    'message' => "Datenbankfehler: " . $e->getMessage()
                ]);
            }
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
                    // Don't START TRANSACTION for single row imports to allow partial success
                    $singleRowImport = isset($data['singleRowImport']) && $data['singleRowImport'] === true;
                    if (!$singleRowImport) {
                        $db->query("START TRANSACTION");
                    }
                    
                    $successCount = 0;
                    $errorCount = 0;
                    $errorLog = [];
                    $insertQuery = $response['insert_query'];

                    $totalRows = count($response['args']);
                    $failedRows = [];
                    $validRowIndices = [];

                    foreach($response['args'] as $index => $args) {
                        try {
                            if ($singleRowImport) {
                                // For single row imports, we don't need transactions
                                $result = $db->query($insertQuery, $args);
                            } else {
                                // For regular imports, use transactions
                                $result = $db->query($insertQuery, $args);
                            }
                            
                            if(isset($result['error'])) {
                                $errorCount++;
                                $failedRows[] = ["row" => $index, "data" => $args, "error" => $result['error']];    
                            } else {
                                $successCount++;
                                // Store the row index (0-based) in validRowIndices
                                $validRowIndices[] = $index;
                            }
                        } catch (Exception $e) {
                            $errorCount++;
                            $failedRows[] = ["row" => $index, "data" => $args, "error" => $e->getMessage()];
                        }
                    }
                
                    if ($singleRowImport) {
                        // For single row imports, just return success if it worked
                        if ($errorCount == 0) {
                            $response = ["status" => "success", "message" => "Datensatz wurde erfolgreich importiert."];
                        } else {
                            $response = [
                                "status" => "error", 
                                "message" => "Fehler beim Import des Datensatzes.",
                                "errors" => $failedRows
                            ];
                        }
                    } else {
                        // For multi-row imports with transaction, commit only if all succeeded
                        if($errorCount == 0) {
                            $db->query("COMMIT");
                            $response = ["status" => "success", "message" => "Alle $successCount Datensätze wurden importiert."];
                        } else {
                            // For partial failures, still report which rows are valid
                            // This is used when the user opts to continue with valid rows only
                            $db->query("ROLLBACK");
                            $response = [
                                "status" => "error", 
                                "message" => "$errorCount von $totalRows Datensätzen fehlgeschlagen.",
                                "errors" => $failedRows,
                                "totalRows" => $totalRows,
                                "validRowIndices" => $validRowIndices
                            ];
                        }
                    }
                }
                catch (Exception $e) {
                    if (!$singleRowImport) {
                        $db->query("ROLLBACK");
                    }
                    $response = ["status" => "error", "message" => "Schwerwiegender Fehler: " . $e->getMessage()];
                }

                ob_end_clean();
                echo json_encode($response);
                exit;
            }
            break;

        default:
            ob_end_clean();
            echo json_encode(['status' => 'error', 'message' => 'Ungültige Aktion']);
            break;
    }
} catch (Exception $e) {
    error_log("Global exception: " . $e->getMessage());
    ob_end_clean();
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}

function checkDaten($data, $db){
    $importDatenzeilen = $data['rows'];

    $importDatensaetze = array();
    $zeilenNummer = 0;
    $FK_Spalten = array();
    foreach($importDatenzeilen as $zeile){
        // Nutze str_getcsv für robustes CSV-Parsing (Komma, Anführungszeichen, Escape)
        $feldArray = str_getcsv($zeile, ',', '"', '\\');
        $feldNummer = 0;
        foreach($feldArray as $feldWert) {
            // Im Header die FK-Spalten identifizieren (Indizes sammeln)
            if($zeilenNummer === 0 && isset($data['suchQueries'][$feldWert])){
                $FK_Spalten[] = $feldNummer;
            }
            // Wenn es eine FK-Spalte ist und nicht Header, teile bei Leerzeichen auf
            if(in_array($feldNummer, $FK_Spalten) && $zeilenNummer > 0) {
                $suchBegriffe = array_filter(explode(' ', trim($feldWert)));
                $importDatensaetze[$zeilenNummer][$feldNummer] = $suchBegriffe;
            } else {
                // Header oder normale Spalte: als einzelner Wert
                $importDatensaetze[$zeilenNummer][$feldNummer][] = trim($feldWert);
            }
            $feldNummer++;
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
                $maxTrefferpunkte = 0;
                foreach($suchString as $id => $suchFeld)
                {
                    $maxTrefferpunkte = max($maxTrefferpunkte, $trefferpunkte); // Soviele Treffer hat der beste Datensatz
                    $trefferpunkte = 0;
                    
                    # Wenn zwei Suchbegriffe angegeben wurden, werden bei einem Treffer beide gefunden
                    # Das bedeutet, dass diese ID zwei trefferpunkte bekommt.
                    # Stimmt bei einer anderen ID nun ein Begriff auch überein,
                    # ist das noch kein alternativer Treffer, da noch nicht ALLE Suchbegriffe gefunden wurden.
                    # Es wird daher zunächst nur ein trefferpunkt abgezogen.
                    # Erst wenn trefferpunkte==0 ist, wird die ID als nicht eindeutig identifizier,
                    # da dann ALLE Suchbegriffe ebenfalls gefunden wurden.

                    // Gehe jeden Import-Suchbegriff (Nadel) durch - alle Nadeln müssen gefunden werden
                    foreach($importDatensaetze[$zeile-1][$FeldIndex] as $importWort){

                        // Wird DIESES Wort im Suchstring (= eine ID) gefunden 
                        if(stripos($suchFeld, $importWort) !== false){
                            $trefferpunkte++;
                            $maxTrefferpunkte = max($maxTrefferpunkte, $trefferpunkte);
                            if(isset($datenSatzArgs[$FeldIndex])) // Wurde diese ID schon gesetzt?
                                if($datenSatzArgs[$FeldIndex] != $id){ // wurde es bereits für eine andere ID gefunden? (Um gesetzt zu sein MUSS maxTrefferpunkte der Anzahl der Suchbegriffe entsprechen, sonst unset)
                                    if($trefferpunkte == $maxTrefferpunkte){ // eine ANDERE id mit gleich vielen Treffern
                                        $ERROR = true;
                                        $ERROR_OVER_ALL = true;
                                        $error_msg .= "<p>Der Import <b>$importFeld</b> in Zeile $zeile ($spalte) liefert kein eindeutiges Ergebnis. Bitte pr&auml;zisieren.</p>";
                                        break;
                                    }
                                }
                                if(!$ERROR && $trefferpunkte == $maxTrefferpunkte){
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