<?php
ob_start();  // Ausgabe-Pufferung starten
require_once(__DIR__ . "/../inc/include.php");

$data = json_decode(file_get_contents('php://input'), true);

// Update-Logik
if ($data['action'] == 'update') {
    $id = $data['id'];
    $field = $data['field'];
    $value = $data['value'];
    $tabelle = $data['tabelle'];

    $query = "UPDATE `$tabelle` SET `$field` = ? WHERE `id` = ?";
    $args = array($value, $id);
    try {
        $result = $db->query($query, $args);
        ob_end_clean();  // Puffer löschen, um saubere JSON-Antwort zu gewährleisten
        echo json_encode(["status" => $result ? "success" : "error"]);
    } catch (Exception $e) {
        ob_end_clean();
        $db->log("Update error: " . $e->getMessage());
        echo json_encode(["status" => "error", "message" => "Fehler beim Update. Stimmt das Datenformat? Für Details siehe log-Tabelle in der Datenbank."]);
    }
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
        ob_end_clean();
        if ($result && count($result) > 0) {
            echo json_encode(["status" => "success", "row" => $result[0]]);
        } else {
            echo json_encode(["status" => "error", "message" => "Keine Zeile gefunden"]);
        }
    } catch (Exception $e) {
        ob_end_clean();
        $db->log("Check error: " . $e->getMessage());
        echo json_encode(["status" => "error", "message" => "Fehler beim Zeilenprüfen."]);
    }
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
        ob_end_clean();
        if ($result) {
            echo json_encode(["status" => "success"]);
        } else {
            $errorInfo = $db->errorInfo();
            $db->log("Insert error: " . json_encode($errorInfo));
            echo json_encode(["status" => "error", "message" => "Fehler beim Einfügen des Datensatzes. Bitte prüfen Sie die log-Tabelle in der Datenbank!"]);
        }
    } catch (Exception $e) {
        ob_end_clean();
        $db->log("Exception: " . $e->getMessage());
        echo json_encode(["status" => "error", "message" => "Fehler beim Einfügen des Datensatzes. Bitte prüfen Sie die log-Tabelle in der Datenbank!"]);
    }
}

// Einfügen eines neuen Datensatzes mit Standardwerten aus der Datenbankschema
if ($data['action'] == 'insert_default') {
    $tabelle = $data['tabelle'];

    // Insert an empty dataset to let the database take the default values
    $query = "INSERT INTO `$tabelle` () VALUES ()";
    try {
        $result = $db->query($query);
        ob_end_clean();
        if ($result) {
            echo json_encode(["status" => "success"]);
        } else {
            $errorInfo = $db->errorInfo();
            $db->log("Insert error: " . json_encode($errorInfo));
            echo json_encode(["status" => "error", "message" => "Fehler beim Einfügen des Datensatzes. Bitte prüfen Sie die log-Tabelle in der Datenbank!"]);
        }
    } catch (Exception $e) {
        ob_end_clean();
        $db->log("Exception: " . $e->getMessage());
        echo json_encode(["status" => "error", "message" => "Fehler beim Einfügen des Datensatzes. Bitte prüfen Sie die log-Tabelle in der Datenbank!"]);
    }
}

// Löschen von ausgewählten Datensätzen
if ($data['action'] == 'delete') {
    $tabelle = $data['tabelle'];
    $ids = $data['ids'];

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $query = "DELETE FROM `$tabelle` WHERE `id` IN ($placeholders)";
    try {
        $result = $db->query($query, $ids);
        ob_end_clean();
        echo json_encode(["status" => "success"]);
    } catch (Exception $e) {
        ob_end_clean();
        $db->log("Delete error: " . $e->getMessage());
        echo json_encode(["status" => "error", "message" => "Fehler beim Löschen der Daten."]);
    }
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
        ob_end_clean();  // Puffer löschen, um saubere JSON-Antwort zu gewährleisten
        echo json_encode(["status" => "success", "duplicates" => $duplicateIds]);
    } catch (Exception $e) {
        ob_end_clean();
        $db->log("Check duplicates error: " . $e->getMessage());
        echo json_encode(["status" => "error", "message" => "Fehler beim Überprüfen auf doppelte Einträge."]);
    }
}
?>
