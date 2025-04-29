<?php 
ob_start();
require_once(__DIR__ . "/../user_includes/all.head.php");
require_once(__DIR__ . "/../user_includes/ajax.head.php");
require_once(__DIR__ . "/../inc/include.php");
ob_clean();

// Set error handling
ini_set('display_errors', 0);
error_reporting(E_ERROR | E_PARSE);


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
        $value = $data['value'];
        $field = $data['field'];
        
        if ($field === 'y_id') {
            $pdo = $db->getPDO();
    
            try {

                // CHECK 1
                // Überprüfe, ob es einen anderen Datensatz mit dieser y_id gibt
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM b_mitglieder WHERE y_id = :yid AND id <> :id");
                $stmt->execute([':yid' => $value, ':id' => $id]);
                $count = $stmt->fetchColumn();
                if ($count == 0) {
                    $db->log("Es gibt keinen Datensatz mit der angegebenen Y-ID, der zusammengeführt werden kann.");
                    throw new Exception("Es gibt keinen Datensatz mit der angegebenen Y-ID, der zusammengeführt werden kann.");
                }

                // CHECK 2
                // Überprüfe, ob das Geburtsdatum der beiden Datensätze übereinstimmt
                $stmt = $pdo->prepare("SELECT geburtsdatum FROM b_mitglieder WHERE y_id = :yid AND id <> :id");
                $stmt->execute([':yid' => $value, ':id' => $id]);
                $otherGeburtsdatum = $stmt->fetchColumn();

                $stmt = $pdo->prepare("SELECT geburtsdatum FROM b_mitglieder WHERE id = :id");
                $stmt->execute([':id' => $id]);
                $currentGeburtsdatum = $stmt->fetchColumn();

                if ($otherGeburtsdatum !== $currentGeburtsdatum) {
                    $db->log("Aus Sicherheitsgründen muss das Geburtsdatum der zu zusammenzuführenden Konten übereinstimmen.");
                    throw new Exception("Aus Sicherheitsgründen muss das Geburtsdatum der zu zusammenzuführenden Konten übereinstimmen.");
                }

                // GO!
                $pdo->beginTransaction();
    
                // 1. Lösche ggf. den alten Datensatz mit dieser y_id (außer dem aktuellen)
                $stmt = $pdo->prepare("DELETE FROM b_mitglieder WHERE y_id = :yid AND id <> :id");
                $stmt->execute([':yid' => $value, ':id' => $id]);
    
                // 2. Setze die y_id beim gewünschten Datensatz
                $stmt = $pdo->prepare("UPDATE b_mitglieder SET y_id = :yid WHERE id = :id");
                $stmt->execute([':yid' => $value, ':id' => $id]);
    
                $pdo->commit();

                // 3. Überprüfe, ob die E-Mail-Adresse im aktuellen Datensatz leer ist
                $stmt = $pdo->prepare("SELECT Mail FROM b_mitglieder WHERE id = :id");
                $stmt->execute([':id' => $id]);
                $currentEmail = $stmt->fetchColumn();

                if (empty($currentEmail)) {
                    // Hole die E-Mail-Adresse aus der Tabelle y_user
                    $stmt = $pdo->prepare("SELECT mail FROM y_user WHERE id = :yid");
                    $stmt->execute([':yid' => $value]);
                    $newEmail = $stmt->fetchColumn();

                    if (!empty($newEmail)) {
                        // Aktualisiere die E-Mail-Adresse im aktuellen Datensatz
                        $stmt = $pdo->prepare("UPDATE b_mitglieder SET email = :email WHERE id = :id");
                        $stmt->execute([':email' => $newEmail, ':id' => $id]);
                    }
                }
    
                echo json_encode(['status' => 'success']);
            } catch (Exception $e) {
                $pdo->rollBack();
                error_log("Transaktionsfehler: " . $e->getMessage());
                echo json_encode(['status' => 'error', 'message' => 'Transaktionsfehler: ' . $e->getMessage()]);
            }
        }
        break;
    }
?>