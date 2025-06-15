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
                $stmt = $pdo->prepare("SELECT id as registrierteMNr, geburtsdatum FROM b_mitglieder WHERE y_id = :yid AND id <> :id");
                $stmt->execute([':yid' => $value, ':id' => $id]);
                $otherRow = $stmt->fetch(PDO::FETCH_ASSOC);
                $otherGeburtsdatum = $otherRow['geburtsdatum'] ?? null;
                // MNr des registrierten MItglieds
                $registrierteMNr = $otherRow['registrierteMNr'];

                // Hole Geburtsdatum für den aktuellen Datensatz
                $stmt = $pdo->prepare("SELECT geburtsdatum FROM b_mitglieder WHERE id = :id");
                $stmt->execute([':id' => $id]);
                $currentRow = $stmt->fetch(PDO::FETCH_ASSOC);
                $currentGeburtsdatum = $currentRow['geburtsdatum'] ?? null;

                if ($otherGeburtsdatum !== $currentGeburtsdatum) {
                    $db->log("Aus Sicherheitsgründen muss das Geburtsdatum der zu zusammenzuführenden Konten übereinstimmen.");
                    throw new Exception("Aus Sicherheitsgründen muss das Geburtsdatum der zu zusammenzuführenden Konten übereinstimmen.");
                }

                // GO!
                $pdo->beginTransaction();
    
                // Ich habe jetzt 2 Einträge. Den manuell hinzugefügten "MAN" und den registrieren "REG"
                // Am Ende soll von REG nur die Credentials bleiben (in y_user), der Rest soll von "MAN" ghenommen werden. So der user-case.
                //
                // MNr | Y-ID | Mail | PW | BSG | Sparten |  
                // --- | ---- | ---- | -- | --- | ------- |
                // MAN |      | m@xx |    | MMM | m1, m2  |
                // REG | 123  | r@xx | ab | RRR | r1, r2  |
                // 
                // ergbit =>
                //
                // MAN | 123  | r@xx | ab | MMM | m1, m2  |
                //

                // 1. Entferne (falls vorhanden) das REG-Mitglied aus der Meldeliste (auch wegen FK-constraint)
                $stmt = $pdo->prepare("DELETE FROM b_meldeliste WHERE MNr = :registrierteMNr");
                $stmt->execute([':registrierteMNr' => $registrierteMNr]);

                // 2. Lösche REG aus b_mitglieder (Mail & PW sind in y_user gespeichert, achte sicherheitshalber darauf, nicht MAN mitzulöschen (id ist von MAN)
                $stmt = $pdo->prepare("DELETE FROM b_mitglieder WHERE y_id = :yid AND id <> :id");
                $stmt->execute([':yid' => $value, ':id' => $id]);

                // 3. Setze die y_id beim gewünschten Datensatz
                $stmt = $pdo->prepare("UPDATE b_mitglieder SET y_id = :yid WHERE id = :id");
                $stmt->execute([':yid' => $value, ':id' => $id]);

                $pdo->commit();

                // Einstellungen des Mitglieds bei der Registrierung überschreiben die Einstellungen des BGS-Verwalters

                // Hole die E-Mail-Adresse aus der Tabelle y_user
                $stmt = $pdo->prepare("SELECT mail FROM y_user WHERE id = :yid");
                $stmt->execute([':yid' => $value]);
                $newEmail = $stmt->fetchColumn();

                // Vorherige Werte laden
                $oldValues = [];
                $fieldsToCheck = ['Mail' => 'Mail', 'Vorname' => 'Vorname', 'Nachname' => 'Nachname', 'Geschlecht' => 'Geschlecht', 'Mailbenachrichtigung' => 'Mailbenachrichtigung'];
                $placeholders = implode(", ", array_map(function($f){return "`$f`";}, $fieldsToCheck));
                $stmt = $pdo->prepare("SELECT $placeholders FROM b_mitglieder WHERE id = :id");
                $stmt->execute([':id' => $id]);
                $oldValues = $stmt->fetch(PDO::FETCH_ASSOC);

                // Neue Werte vorbereiten
                $newValues = $oldValues;
                $changedFields = [];

                // Mail aktualisieren
                if (!empty($newEmail)) {
                    if ($oldValues['Mail'] !== $newEmail) {
                        $changedFields['Mail'] = [$oldValues['Mail'], $newEmail];
                        $newValues['Mail'] = $newEmail;
                    }
                    $stmt = $pdo->prepare("UPDATE b_mitglieder SET Mail = :email WHERE id = :id");
                    $stmt->execute([':email' => $newEmail, ':id' => $id]);
                }

                // 4. Weitere Felder aus y_user_details übernehmen (ohne Geburtsdatum)
                $fieldMap = [
                    'vname' => 'Vorname',
                    'nname' => 'Nachname',
                    'geschlecht' => 'Geschlecht',
                    'okformail' => 'Mailbenachrichtigung'
                ];
                foreach ($fieldMap as $yField => $bField) {
                    $stmt = $pdo->prepare("
                        SELECT d.fieldvalue
                        FROM y_user_details d
                        JOIN y_user_fields f ON d.fieldID = f.ID
                        WHERE d.userID = :yid AND f.fieldname = :fieldname
                        LIMIT 1
                    ");
                    $stmt->execute([':yid' => $value, ':fieldname' => $yField]);
                    $fieldValue = $stmt->fetchColumn();
                    if ($fieldValue !== false && $fieldValue !== null && $oldValues[$bField] != $fieldValue) {
                        $changedFields[$bField] = [$oldValues[$bField], $fieldValue];
                        $newValues[$bField] = $fieldValue;
                        $stmtUpdate = $pdo->prepare("UPDATE b_mitglieder SET `$bField` = :val WHERE id = :id");
                        $stmtUpdate->execute([':val' => $fieldValue, ':id' => $id]);
                    }
                }

                // Für die Rückmeldung: Werte ggf. lesbar machen
                $labelMap = [
                    'Mail' => 'Mail',
                    'Vorname' => 'Vorname',
                    'Nachname' => 'Nachname',
                    'Geschlecht' => 'Geschlecht',
                    'Mailbenachrichtigung' => 'Mailbenachrichtigung'
                ];
                // Für Geschlecht und Mailbenachrichtigung ggf. Mapping auf Text
                $geschlechtMap = [1 => 'männlich', 2 => 'weiblich', 3 => 'divers'];
                $mailbenachrichtigungMap = [1 => 'JA', 2 => 'NEIN'];
                $formatValue = function($field, $val) use ($geschlechtMap, $mailbenachrichtigungMap) {
                    if ($field === 'Geschlecht') {
                        return $geschlechtMap[$val] ?? $val;
                    }
                    if ($field === 'Mailbenachrichtigung') {
                        return $mailbenachrichtigungMap[$val] ?? $val;
                    }
                    return $val;
                };

                $changesText = "";
                foreach ($changedFields as $field => [$old, $new]) {
                    $changesText .= $labelMap[$field] . ": " . $formatValue($field, $old) . " -> " . $formatValue($field, $new) . "\n";
                }

                $msg = "Die Datensätze wurden erfolgreich zusammengelegt.";
                if ($changesText) {
                    $msg .= " Folgende Felder haben nicht übereingestimmt und wurden wie folgt gesetzt. Korrekturen sind jederzeit unter 'Stammdaten bearbeiten' möglich.\n\n" . $changesText . "\nUm die Anzeige zu aktualisieren, bitte die Daten neu laden (oben links).";
                } else {
                    $msg .= " Es wurden keine Felder geändert.\nUm die Anzeige zu aktualisieren, bitte die Daten neu laden (oben links).";
                }

                echo json_encode(['status' => 'success', 'message' => $msg, 'success_alert' => 1]);
            } catch (Exception $e) {
                $pdo->rollBack();
                error_log("Transaktionsfehler: " . $e->getMessage());
                echo json_encode(['status' => 'error', 'message' => 'Transaktionsfehler: ' . $e->getMessage()]);
            }
        }
        break;
    }
?>