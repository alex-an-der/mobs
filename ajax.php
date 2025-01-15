<?php
ob_start();  // Ausgabe-Pufferung starten vor allen includes
require_once(__DIR__ . "/mods/all.head.php");
require_once(__DIR__ . "/mods/ajax.head.php");
require_once(__DIR__ . "/inc/include.php");
ob_clean();  // Löschen aller bisherigen Ausgaben

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

switch($action) {
    case 'update':
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
        break;

    case 'check':
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
        break;

    case 'insert':
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
        break;

    case 'insert_default':
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
        break;

    case 'delete':
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
        break;

    case 'check_duplicates':
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
        break;

    case 'import':
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
        break;

    case 'matchForeignKey':
        $query = $data['query'] ?? '';
        $value = $data['value'] ?? '';
        
        // Debug-Informationen sammeln
        $debugInfo = [
            'query' => $query,
            'searchValue' => $value,
            'searchTerms' => array_filter(explode(' ', strtolower($value))),
            'resultData' => []
        ];
        
        $matches = findForeignKeyMatches($db, $value, $query, $debugInfo);
        
        if (empty($matches)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Keine Matches gefunden für: ' . htmlspecialchars($value),
                'debug' => $debugInfo
            ]);
        } else if (count($matches) > 1) {
            $matchDetails = array_map(function($match) {
                return $match['anzeige'];
            }, $matches);
            
            echo json_encode([
                'status' => 'error',
                'message' => 'Mehrere mögliche Matches gefunden für: ' . htmlspecialchars($value) . 
                            '<br>Gefundene Matches: ' . implode(', ', $matchDetails),
                'debug' => $debugInfo
            ]);
        } else {
            echo json_encode([
                'status' => 'success',
                'id' => $matches[0]['id'],
                'debug' => $debugInfo
            ]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Ungültige Aktion']);
        break;
}

// Neue Funktion die alle Matches zurückgibt
function findForeignKeyMatches($db, $searchValue, $referenzquery, &$debugInfo) {
    
    /*
    $anzuzeigendeDaten[] = array(
    "tabellenname" => "b_mitglieder_in_sparten",
    "auswahltext" => "Mitglieder in den Sparten",
    "query" => "SELECT mis.id as id, mis.Sparte as Sparte, mis.Mitglied as Mitglied
                from b_mitglieder_in_sparten as mis
                left join v_verbands_berechtigte_sparte as vbs on vbs.Sparte = mis.Sparte
                where vbs.Verbandsberechtigter = $uid or mis.Sparte is NULL 
                order by mis.id desc;
    ",
    "referenzqueries" => array(
        "Sparte" => "SELECT Sparte as id, Sparte_Name as anzeige
                    from v_verbands_berechtigte_sparte
                    where Verbandsberechtigter = $uid
                    ORDER BY anzeige;
        ",
        "Mitglied" => "SELECT m.id as id, CONCAT(m.Nachname, ', ', m.Vorname, ' (', vbr.BSG_Name,')') as anzeige 
                        from b_mitglieder as m
                        join v_verbands_berechtigte_bsg as vbr on m.BSG = vbr.BSG
                        where vbr.Verbandsberechtigter = $uid
                        ORDER BY anzeige;
        "
    ),
    "suchqueries" => array(
        "Sparte" => "SELECT *
                    from v_verbands_berechtigte_sparte
                    where Verbandsberechtigter = $uid;
        ",
        "Mitglied" => "SELECT * 
                        from b_mitglieder as m
                        join v_verbands_berechtigte_bsg as vbr on m.BSG = vbr.BSG
                        where vbr.Verbandsberechtigter = $uid;
        "
    )
        */
    return $matches;
}
?>
