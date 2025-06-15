<?php
ob_start();
require_once(__DIR__ . "/inc/include.php");
ob_clean();

// ACHTUNG! Nicht in die ajax.php packen, sonst kann es einen infinity-loop geben, wenn HIER was nicht klappt!
// Dieses Fehlermanagement darf NUR an Aufrufen in Richtung ajax.php odr custom-ajax gehen!

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

    switch($action) {
        case 'new_error':
            $args = array();
            $args[] = $data['src'];
            $args[] = $data['errorcode'];
            $args[] = $data['errorMessage'];
            $query=("INSERT INTO sys_error_manager (source, sql_error_code, raw_message) VALUES (?, ?, ?);");

            try {
                if($db->query($query, $args)) $response = ["status" => "success"];
                else                          $response = ["status" => "error", "message" => "Konnte neuen Fehlercode nicht eintragen"];
            } catch (Exception $e) {
                logError($e);
                $response = ["status" => "error", "message" => "Konnte neuen Fehlercode nicht eintragen"];
            }
            ob_end_clean();
            echo json_encode($response);

            break;

            default:
                ob_end_clean();
                echo json_encode(['status' => 'error', 'message' => 'Ungültige Aktion']);
            break;
    }



} catch (Exception $e) {
    error_log("Global exception in error_management: " . $e->getMessage());
    ob_end_clean();
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}

function logError($e){
    $db->log("Check error: " . $e->getMessage());
}

?>