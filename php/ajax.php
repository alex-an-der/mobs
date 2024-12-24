<?php
ob_start();  // Startet die Ausgabe-Pufferung
require_once(__DIR__ . "/../inc/include.php");
ob_end_clean();  // Löscht unerwünschte Ausgaben (z. B. HTML)

$data = json_decode(file_get_contents('php://input'), true);

if ($data['action'] == 'update') {
    $id = $data['id'];
    $field = $data['field'];
    $value = $data['value'];
    $tabelle = $data['tabelle'];

    // Update-Abfrage mit Backticks
    $query = "UPDATE `$tabelle` SET `$field` = ? WHERE `id` = ?";
    $args = array($value, $id);
    $result = $db->query($query, $args);

    // JSON-Antwort senden (Erfolg oder Fehler)
    echo json_encode(["status" => $result ? "success" : "error"]);
}

if ($data['action'] == 'check') {
    $id = $data['id'];
    $tabelle = $data['tabelle'];

    // Zeile aus der DB abfragen
    $query = "SELECT * FROM `$tabelle` WHERE `id` = ?";
    $args = array($id);
    $result = $db->query($query, $args);

    if ($result && count($result) > 0) {
        echo json_encode(["status" => "success", "row" => $result[0]]);
    } else {
        echo json_encode(["status" => "error", "message" => "Keine Zeile gefunden"]);
    }
}
?>
