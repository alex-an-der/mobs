<?php
ob_start();  // Ausgabe-Pufferung starten vor allen includes
require_once(__DIR__ . "/mods/all.head.php");
require_once(__DIR__ . "/mods/ajax.head.php");
require_once(__DIR__ . "/inc/include.php");
ob_clean();  // Löschen aller bisherigen Ausgaben

$data = json_decode(file_get_contents('php://input'), true);

// Update-Logik
if ($data['action'] == 'update') {
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
}

// Zeilenprüfung nach dem Update
if ($data['action'] == 'check') {
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
}

// Einfügen eines neuen Datensatzes mit Standardwerten
if ($data['action'] == 'insert') {
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
}

// Einfügen eines neuen Datensatzes mit Standardwerten aus der Datenbankschema
if ($data['action'] == 'insert_default') {
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
}

// Löschen von ausgewählten Datensätzen
if ($data['action'] == 'delete') {
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
}

// Überprüfen auf doppelte Einträge
if ($data['action'] == 'check_duplicates') {
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
}

// Importieren von Datensätzen
if ($data['action'] == 'import') {
    $tabelle = $data['tabelle'];
    $header = $data['header'];
    $values = $data['values'];
    
    try {
        // Baue INSERT Query
        $columns = implode(', ', $header);
        $valueStrings = [];
        
        foreach($values as $row) {
            $rowValues = array_map(function($val) {
                if($val === '') return 'NULL';
                return "'" . addslashes($val) . "'";
            }, $row);
            $valueStrings[] = '(' . implode(', ', $rowValues) . ')';
        }
        
        $valuesSql = implode(",\n", $valueStrings);
        $query = "INSERT INTO $tabelle ($columns) VALUES $valuesSql";
        
        $result = $db->query($query);
        
        if(isset($result['error'])) {
            $response = ['status' => 'error', 'message' => $result['error']];
        } else {
            $count = count($values);
            $response = ['status' => 'success', 'message' => "$count Datensätze wurden importiert"];
        }
    } catch(Exception $e) {
        $response = ['status' => 'error', 'message' => $e->getMessage()];
    }
    ob_end_clean();
    echo json_encode($response);
}
?>
