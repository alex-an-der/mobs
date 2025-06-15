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
        case 'error_occured':

            // Aufgabe: Speicher Fehler in DB, gebe Fehlermeldung zurück.
            // 1. Hole Meldung zum code
            $query = "SELECT user_message, add_fulltext_constraint FROM sys_error_manager WHERE sql_error_code = ?";
            $args = array();
            $args[] = $data['errorcode'];
            try {
                $response = $db->query($query, $args, false);
                $user_message = "";
                // Prüfe alle gefundenen $user_message auf eine zusätzliche Volltextvorgabe (z.B. um bestimmte DB-Constraints zu identifizieren und die Fehlermeldung noch granularer auszugeben
                // Bevorzuge dabei MIT add_fulltext_constraint bei mehrfachen Ergebnissen
                
                foreach($response['data'] as $fehlerDaten){
                    if(!empty($fehlerDaten['user_message'])){
                    // Es gibt zum code eine user_message
                        if(empty($fehlerDaten['add_fulltext_constraint'])){
                        // Es gibt KEINEN fulltext-constraint
                            $user_message = $fehlerDaten['user_message'];
                        }elseif (strpos($data['errorMessage'], $fehlerDaten['add_fulltext_constraint']) !== false) {
                            // Es gibt einen full-text-constraint, der passt => Behalte diese Meldung, was besseres kommt nicht!
                            $user_message = $fehlerDaten['user_message'];
                            break;
                        }
                        // Medldung mit unpassendem full-text-constraint werden ignoriert.
                    }
                }

                if (empty($user_message)) {
                    $response = ["status" => "error", "message" => "Kein user_message für diesen Fehlercode gefunden."];
                }else{
                    $response = ["status" => "success", "message" => $user_message];
                    // Ende
                    ob_end_clean();
                    echo json_encode($response);
                    break;
                }
            } catch (Exception $e) {
                logError($e);
                $response = ["status" => "error", "message" => "Konnte user_message nicht aus sys_error_manager holen."];
            }

            // 2. Wenn es keine Meldung gibt: Speichere den Fehlercode (wenn es ihn schon gibt, weist UNIQUE ihn zurück
            $args = array();
            $args[] = $data['src'];
            $args[] = $data['errorcode'];
            $args[] = $data['errorMessage'];
            $query=("INSERT INTO sys_error_manager (source, sql_error_code, raw_message) VALUES (?, ?, ?);");

            try {
                $db->query($query, $args);
            } catch (Exception $e) {
                logError($e);
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