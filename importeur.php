<?php
require_once(__DIR__ . "/mods/all.head.php");
require_once(__DIR__ . "/mods/index.head.php");
require_once(__DIR__ . "/inc/include.php");

$admin = 1;
$selectedTableID = isset($_GET['tab']) ? $_GET['tab'] : "";
$data = array();
$hasForeignKeys = false;
$foreignKeyColumns = array();

if(isset($anzuzeigendeDaten[$selectedTableID])){
    // Tabellenname existiert?
    if(isset($anzuzeigendeDaten[$selectedTableID]['tabellenname'])){
        $tabelle = $anzuzeigendeDaten[$selectedTableID]['tabellenname'];
    }else{
        $err = "Die Konstante \$anzuzeigendeDaten[$selectedTableID]['tabellenname'] enth&auml;lt keinen g&uuml;ltigen Tabellennamen oder existiert nicht.";
        dieWithError($err,__FILE__,__LINE__);
    }
    // Query existiert?
    if(isset($anzuzeigendeDaten[$selectedTableID]['query'])){
        $dataquery = $anzuzeigendeDaten[$selectedTableID]['query'];
    }else{
        $err = "Die Konstante \$anzuzeigendeDaten[$selectedTableID]['query'] enth&auml;lt keinen g&uuml;ltigen Tabellennamen oder existiert nicht.";
        dieWithError($err,__FILE__,__LINE__);
    }

    // Prüfen auf FK-Spalten
    if(isset($anzuzeigendeDaten[$selectedTableID]['referenzqueries'])) {
        $hasForeignKeys = true;
        $foreignKeyColumns = array_keys($anzuzeigendeDaten[$selectedTableID]['referenzqueries']);
    }

    echo "<div class='container mt-4'>";
    if($hasForeignKeys) {
        echo "<div class='alert alert-info'>";
        echo "Diese Tabelle hat folgende Fremdschlüssel-Spalten: " . implode(", ", $foreignKeyColumns);
        echo "</div>";
    } else {
        echo "<div class='alert alert-info'>";
        echo "Diese Tabelle hat keine Fremdschlüssel-Spalten.";
        echo "</div>";
    }
    echo "</div>";

    if(!$hasForeignKeys) {
        // Hole Spalteninformationen
        $columns = $db->query("SHOW COLUMNS FROM $tabelle");
        if(!isset($columns['data'])) {
            dieWithError("Konnte Spalteninformationen nicht abrufen", __FILE__, __LINE__);
        }

        $tableColumns = array();
        foreach($columns['data'] as $col) {
            if($col['Extra'] != 'auto_increment') {
                $tableColumns[] = $col['Field'];
            }
        }
    }
}

// Helper functions
function dieWithError($err, $file, $line, $stayAlive = false) {
    global $db;
    $db->log("$file:$line - $err");
    echo("<br><div class='container'><b>Konfigurationsfehler:</b> $err</div>");
    if(!$stayAlive) die();
}

function renderTableSelectBox($db) {
    global $anzuzeigendeDaten, $selectedTableID;
    $options = [];
    
    if(!isset($anzuzeigendeDaten[$selectedTableID])){
        $options[] = ['value' => '', 'text' => '-- Tabelle wählen --', 'selected' => true];
    }

    foreach ($anzuzeigendeDaten as $index => $table) {
        $displayText = !empty($table['auswahltext']) ? $table['auswahltext'] : $table['tabellenname'];
        $options[] = [
            'value' => $index,
            'text' => $displayText,
            'selected' => ($index == $selectedTableID)
        ];
    }
    
    return $options;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=TITEL?></title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script>
        // Globale Variablen für PHP-Werte
        const validColumns = <?= json_encode($tableColumns ?? []) ?>;
        
        function validateImport() {
            const textarea = document.getElementById('importData');
            const data = textarea.value.trim();
            const lines = data.split('\n');
            
            if(lines.length < 2) {
                showValidationResult(false, 'Fehler: Mindestens Header und ein Datensatz erforderlich');
                return;
            }

            const header = parseCSVLine(lines[0]);
            
            // Prüfe nur ob die verwendeten Spalten gültig sind
            for(let col of header) {
                if(!validColumns.includes(col)) {
                    showValidationResult(false, 'Fehler: Ungültige Spalte im Header: ' + col + '<br>Erlaubte Spalten sind: ' + validColumns.join(', '));
                    return;
                }
            }

            // Prüfe Datensätze
            for(let i = 1; i < lines.length; i++) {
                if(lines[i].trim() === '') continue;
                
                const fields = parseCSVLine(lines[i]);
                if(fields.length !== header.length) {
                    showValidationResult(false, `Fehler: Zeile ${i+1} hat eine falsche Anzahl Felder (${fields.length} statt ${header.length})`);
                    return;
                }
            }

            showValidationResult(true, 'Datenformat ist korrekt! Der Import kann durchgeführt werden.');
        }

        function showValidationResult(isValid, message) {
            const resultDiv = document.getElementById('validationResult');
            resultDiv.style.display = 'block';
            resultDiv.className = 'alert ' + (isValid ? 'alert-success' : 'alert-danger');
            resultDiv.innerHTML = message;
        }

        function parseCSVLine(line) {
            const fields = [];
            let field = '';
            let inQuotes = false;
            
            for(let i = 0; i < line.length; i++) {
                const char = line[i];
                
                if(char === '\"') {
                    inQuotes = !inQuotes;
                } else if(char === ',' && !inQuotes) {
                    fields.push(field.trim());
                    field = '';
                } else {
                    field += char;
                }
            }
            
            fields.push(field.trim());
            return fields;
        }
    </script>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="container mt-4">
            <!-- Table Select -->
            <form method="get">
                <select name="tab" class="form-control" onchange="this.form.submit()">
                    <?php foreach(renderTableSelectBox($db) as $option): ?>
                        <option value="<?= htmlspecialchars($option['value']) ?>" 
                                <?= $option['selected'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($option['text']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>

            <!-- Hints -->
            <?php if(isset($anzuzeigendeDaten[$selectedTableID]['hinweis'])): ?>
                <div class="alert alert-info">
                    <?= $anzuzeigendeDaten[$selectedTableID]['hinweis'] ?>
                </div>
            <?php endif; ?>

            <!-- Import Section -->
            <?php if(!$hasForeignKeys && isset($tableColumns)): ?>
                <div class="mt-4">
                    <h4>Datenimport für: <?= htmlspecialchars($anzuzeigendeDaten[$selectedTableID]['auswahltext']) ?></h4>
                    
                    <!-- Import Rules -->
                    <div class="alert alert-warning">
                        <p><strong>Ihre Tabelle verwendet folgende Spalten:</strong></p>
                        <p>Header kann folgende Spalten enthalten:</p>
                        <p><code><strong><?= implode(",", $tableColumns) ?></strong></code></p>
                        <p class="mt-2">Beispiel mit fehlenden Werten:</p>
                        <code>Anrede,Vorname,Nachname<br>Herr,,Meier<br>Frau,Lisa,</code>
                        <p class="mt-2"><small>Im Beispiel bleiben die Felder 'Vorname' bzw. 'Nachname' leer (NULL)</small></p>
                    </div>

                    <!-- Import Form -->
                    <div class="form-group">
                        <textarea id="importData" class="form-control" rows="10" 
                                placeholder="z.B.:&#10;Name,Alter,Stadt&#10;Max Müller,42,Berlin&#10;'Mustermann, Peter',23,Hamburg"></textarea>
                    </div>
                    
                    <div id="validationResult" class="alert" style="display:none;"></div>
                    <button onclick="validateImport()" class="btn btn-primary">Daten prüfen</button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
