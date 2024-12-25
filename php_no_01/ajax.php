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
    $result = $db->query($query, $args);

    ob_end_clean();  // Puffer löschen, um saubere JSON-Antwort zu gewährleisten
    echo json_encode(["status" => $result ? "success" : "error"]);
}

// Zeilenprüfung nach dem Update
if ($data['action'] == 'check') {
    $id = $data['id'];
    $field = $data['field'];
    $tabelle = $data['tabelle'];

    $query = "SELECT * FROM `$tabelle` WHERE `id` = ?";
    $args = array($id);
    $result = $db->query($query, $args);

    ob_end_clean();

    if ($result && count($result) > 0) {
        echo json_encode(["status" => "success", "row" => $result[0]]);
    } else {
        echo json_encode(["status" => "error", "message" => "Keine Zeile gefunden"]);
    }
}

// Einfügen eines neuen Datensatzes
if ($data['action'] == 'insert') {
    $tabelle = $data['tabelle'];
    unset($data['action']);
    unset($data['tabelle']);

    $fields = array_keys($data);
    $placeholders = array_fill(0, count($fields), '?');
    $values = array_values($data);

    // Query the database for the expected data types
    $query = "DESCRIBE `$tabelle`";
    $columns = $db->query($query);

    // Parse the data according to the expected data types
    foreach ($columns as $column) {
        $field = $column['Field'];
        $type = $column['Type'];

        if (isset($data[$field])) {
            if (strpos($type, 'int') !== false) {
                $data[$field] = (int)$data[$field];
            } elseif (strpos($type, 'float') !== false || strpos($type, 'double') !== false || strpos($type, 'decimal') !== false) {
                $data[$field] = (float)$data[$field];
            } elseif (strpos($type, 'varchar') !== false || strpos($type, 'text') !== false) {
                $data[$field] = (string)$data[$field];
            }
        }
    }

    $values = array_values($data);

    $query = "INSERT INTO `$tabelle` (" . implode(',', $fields) . ") VALUES (" . implode(',', array_map(function($value) {
        return is_numeric($value) ? $value : "'$value'";
    }, $values)) . ")";
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
        echo json_encode(["status" => "error", "message" => "Exception: " . $e->getMessage()]);
    }
}
?>
