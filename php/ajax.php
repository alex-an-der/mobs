<?php
ob_start();  // Ausgabe-Pufferung starten
require_once(__DIR__ . "/../inc/include.php");

$data = json_decode(file_get_contents('php://input'), true);

if ($data['action'] == 'update') {
    $id = $data['id'];
    $field = $data['field'];
    $value = $data['value'];
    $tabelle = $data['tabelle'];

    // Update-Abfrage mit Platzhaltern
    $query = "UPDATE `$tabelle` SET `$field` = ? WHERE `id` = ?";
    $args = array($value, $id);
    $result = $db->query($query, $args);

    ob_end_clean();  // Puffer löschen, um saubere JSON-Antwort zu gewährleisten
    echo json_encode(["status" => $result ? "success" : "error"]);
}

if ($data['action'] == 'check') {
    $id = $data['id'];
    $field = $data['field'];
    $value = $data['value'];
    $tabelle = $data['tabelle'];

    // Zeile mit allen Spalten abrufen
    $query = "SELECT * FROM `$tabelle` WHERE `id` = ?";
    $args = array($id);
    $result = $db->query($query, $args);

    ob_end_clean();  // Puffer löschen, um saubere JSON-Antwort zu gewährleisten

    // Wenn die Zeile gefunden wurde
    if ($result && count($result) > 0) {
        echo json_encode(["status" => "success", "row" => $result[0]]);
    } else {
        echo json_encode(["status" => "error", "message" => "Keine Zeile gefunden"]);
    }
}
?>
