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
?>
