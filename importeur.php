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

        if (isset($anzuzeigendeDaten[$selectedTableID]['suchqueries'])) {
            $suchQueries = $anzuzeigendeDaten[$selectedTableID]['suchqueries'];
        } elseif (isset($anzuzeigendeDaten[$selectedTableID]['referenzqueries'])) {
            $suchQueries = $anzuzeigendeDaten[$selectedTableID]['referenzqueries'];
        } else {
            $suchQueries =  "";
        }
        $suchQueries = array_map(function($query) {
            return preg_replace('/\s+/', ' ', trim($query));
        }, $suchQueries);
        // show($suchQueries);
        
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

// Helper für FK-Matching
function findForeignKeyMatch($db, $searchValue, $referenzquery) {
    $result = $db->query($referenzquery);
    if (!isset($result['data'])) return null;

    $searchTerms = array_filter(explode(' ', strtolower($searchValue)));
    $matches = [];

    foreach ($result['data'] as $row) {
        $allFieldsMatch = true;
        $allFields = strtolower(implode(' ', $row));
        
        foreach ($searchTerms as $term) {
            if (strpos($allFields, $term) === false) {
                $allFieldsMatch = false;
                break;
            }
        }
        
        if ($allFieldsMatch) {
            $matches[] = $row['id'];
        }
    }

    return count($matches) === 1 ? $matches[0] : null;
    }


?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=TITEL?></title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .textarea-container {
            display: flex;
            position: relative;
            margin-bottom: 1rem;
        }
        .line-numbers {
            width: 3em;
            border-radius: 0.25rem 0 0 0.25rem;
            border-right: none;
            text-align: right;
            color: #6c757d;
            background-color: #f8f9fa;
            resize: none;
            cursor: default;
            user-select: none;
            font-family: monospace;
            padding-right: 0.5rem;
        }
        #importData {
            flex-grow: 1;
            border-radius: 0 0.25rem 0.25rem 0;
            font-family: monospace;
            resize: vertical;
        }
        /* Gemeinsame Styles für beide Textareas */
        .line-numbers, #importData {
            font-size: 1rem;
            line-height: 1.5;
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
            min-height: 200px;
        }
    </style>

    
    <script>
        // Globale Variablen für PHP-Werte 
        const validColumns = <?= json_encode($tableColumns ?? []) ?>;
        const hasForeignKeys = <?= json_encode($hasForeignKeys ?? false) ?>;  // Diese Zeile hinzufügen
        
        /*function parseCSV(str) {
            const arr = [];
            let quote = false;  // 'true' means we're inside a quoted field

            // Iterate over each character, keep track of current row and column (of the returned array)
            for (let row = 0, col = 0, c = 0; c < str.length; c++) {
                let cc = str[c], nc = str[c+1];        // Current character, next character
                arr[row] = arr[row] || [];             // Create a new row if necessary
                arr[row][col] = arr[row][col] || '';   // Create a new column (start with empty string) if necessary

                // If the current character is a quotation mark, and we're inside a
                // quoted field, and the next character is also a quotation mark,
                // add a quotation mark to the current column and skip the next character
                if (cc == '"' && quote && nc == '"') { arr[row][col] += cc; ++c; continue; }

                // If it's just one quotation mark, begin/end quoted field
                if (cc == '"') { quote = !quote; continue; }

                // If it's a comma and we're not in a quoted field, move on to the next column
                if (cc == ',' && !quote) { ++col; continue; }

                // If it's a newline (CRLF) and we're not in a quoted field, skip the next character
                // and move on to the next row and move to column 0 of that new row
                if (cc == '\r' && nc == '\n' && !quote) { ++row; col = 0; ++c; continue; }

                // If it's a newline (LF or CR) and we're not in a quoted field,
                // move on to the next row and move to column 0 of that new row
                if (cc == '\n' && !quote) { ++row; col = 0; continue; }
                if (cc == '\r' && !quote) { ++row; col = 0; continue; }

                // Otherwise, append the current character to the current column
                arr[row][col] += cc;
            }
            return arr;
        }*/

        function validateImport(insert=false) {
            const textarea = document.getElementById('importData');
            const data = textarea.value.trim();
            const lines = data.split('\n');
            action = 'validate';
            if(insert){
                action = 'import';
            }
            
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

            allRows = [];
            allRows.push(lines[0]);
            // Prüfe Datensätze
            for(let i = 1; i < lines.length; i++) {
                if(lines[i].trim() === '') continue;
                allRows.push(lines[i]);
                const fields = parseCSVLine(lines[i]);
                if(fields.length !== header.length) {
                    showValidationResult(false, `Fehler: Zeile ${i+1} hat eine falsche Anzahl Felder (${fields.length} statt ${header.length})`);
                    return;
                }
            }

            // FK-Validierung hinzufügen
            if (hasForeignKeys) {

                const queries = <?= json_encode($suchQueries)?>;
                const tabelle = <?= json_encode($tabelle)?>

                if (!queries) {
                    showValidationResult(false, 'Import wegen mangelnder Konfigurationseinstellungen nicht möglich');
                    return;
                }

                fetch('ajax.php', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: action,
                        rows: allRows,
                        suchQueries: queries,
                        tabelle: tabelle
                    })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.status === 'error') {
                        ausgabe = result.message;
                        if (typeof result.errors !== 'undefined'){ console.log(result.errors);
                            ausgabe += "<p>" + JSON.stringify(result.errors) + "</p>";
                        }
                        showValidationResult(false, ausgabe);
                    } else {
                        if(insert){
                            showValidationResult(true, result.message);
                        }else{
                            showValidationResult(true, 'Die Daten k&ouml;nnen so importiert werden.');

                        }
                    }
                })
                .catch(error => {
                    showValidationResult(false, 'Fehler bei der Fremdschlüssel-Validierung: ' + error.message);
                });
            }

            showValidationResult(true, 'Datenformat ist korrekt! Der Import kann durchgeführt werden.');
        }

        function showValidationResult(isValid, message) {
            const resultDiv = document.getElementById('validationResult');
            const importButton = document.getElementById('importButton');
            const importHelpContent = document.getElementById('importHelpContent');
            
            resultDiv.style.display = 'block';
            resultDiv.className = 'alert ' + (isValid ? 'alert-success' : 'alert-danger');
            resultDiv.innerHTML = message;
            
            if (isValid) {
                importButton.style.display = 'inline-block';
                importHelpContent.classList.remove('show');
            } else {
                importButton.style.display = 'none';
                importHelpContent.classList.add('show');
            }
        }

        function importData() {
            const allRows = lines.map(line => parseCSVLine(line));
            const queries = <?= json_encode($suchQueries)?>;
            const tabelle = <?= json_encode($tabelle)?>

            /*const textarea = document.getElementById('importData');
            const data = textarea.value.trim();
            const lines = data.split('\n');
            const header = parseCSVLine(lines[0]);
            const values = lines.slice(1)
                .filter(line => line.trim())
                .map(line => parseCSVLine(line));*/

            const importButton = document.getElementById('importButton');
            importButton.disabled = true;
            importButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Importiere...';

            fetch('ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    /*action: 'import',
                    tabelle: '< ?= $tabelle ?>',
                    header: header,
                    values: values*/

                    action: 'import',
                    rows: allRows,
                    suchQueries: queries,
                    tabelle: tabelle
                })
            })
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    showValidationResult(true, result.message);
                } else {
                    showValidationResult(false, 'Fehler beim Import: ' + result.message);
                }
            })
            .catch(error => {
                showValidationResult(false, 'Fehler beim Import: ' + error.message);
            })
            .finally(() => {
                importButton.disabled = false;
                importButton.innerHTML = 'Daten importieren';
            });
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

        function updateLineNumbers() {
            const textarea = document.getElementById('importData');
            const lineNumbers = document.getElementById('line-numbers');
            const lines = textarea.value.split('\n').length;
            lineNumbers.value = Array.from({length: lines}, (_, i) => i + 1).join('\n');
            
            // Synchronisiere Scroll und Höhe
            lineNumbers.style.height = textarea.offsetHeight + 'px';
            lineNumbers.scrollTop = textarea.scrollTop;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const textarea = document.getElementById('importData');
            textarea.addEventListener('input', updateLineNumbers);
            textarea.addEventListener('scroll', function() {
                document.getElementById('line-numbers').scrollTop = this.scrollTop;
            });
            
            // Initial update
            updateLineNumbers();
        });
    </script>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="container mt-4">
            <!-- Table Select -->
            <form method="get" class="d-flex align-items-center">
                <select name="tab" class="form-control mr-2" onchange="this.form.submit()">
                    <?php foreach(renderTableSelectBox($db) as $option): ?>
                        <option value="<?= htmlspecialchars($option['value']) ?>" 
                                <?= $option['selected'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($option['text']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <a href="index.php?tab=<?= $selectedTableID ?>" class="btn btn-secondary">Zurück</a>
            </form>

            <!-- Hints -->
            <?php if(isset($anzuzeigendeDaten[$selectedTableID]['hinweis'])): ?>
                <div class="alert alert-info">
                    <?= $anzuzeigendeDaten[$selectedTableID]['hinweis'] ?>
                </div>
            <?php endif; ?>

            <!-- Import Section -->
            <?php if(isset($tableColumns)): ?>
                <div class="mt-4">
                    <h4>Datenimport für: <?= htmlspecialchars($anzuzeigendeDaten[$selectedTableID]['auswahltext']) ?></h4>
                    
                    <!-- Import Rules -->
                    <div class="alert alert-warning" id="importHelp">
                        <p class="mb-0">
                            <button class="btn btn-link p-0" type="button" data-toggle="collapse" data-target="#importHelpContent">
                                Hilfe zum Import anzeigen/ausblenden
                            </button>
                        </p>
                        <div class="collapse show" id="importHelpContent">
                            <p class="mt-3"><strong>Anleitung zum Import:</strong></p>
                            <ol>
                                <p>1. Kopfzeile erstellen mit den Spalten:</p>
                                <p><code><strong><?= implode(",", $tableColumns) ?></strong></code></p>
                                
                                <p>2. Daten einfügen:</p>
                                <ul>
                                    <li>Eine Zeile pro Datensatz</li>
                                    <li>Spalten durch Komma trennen</li>
                                    <li>Leere Felder: einfach nichts zwischen die Kommas schreiben</li>
                                    <?php if($hasForeignKeys): ?>
                                    <li>Bei Fremdschlüssel-Spalten (<?= implode(", ", $foreignKeyColumns) ?>):
                                        <ul>
                                            <li>Namen oder Bezeichnung eingeben (z.B. "Hans Müller" oder "Abteilung Nord")</li>
                                            <li>Teilwörter reichen aus (z.B. "Hans" oder "Nord")</li>
                                            <li>Groß/Kleinschreibung spielt keine Rolle</li>
                                            <li>Die Eingabe muss eindeutig auf einen Datensatz passen</li>
                                        </ul>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                                
                                <p>3. Beispiel mit Daten:</p>
                                <?php if($hasForeignKeys): ?>
                                <code>Vorname,Nachname,Abteilung<br>Hans,Meier,Nord<br>Lisa,Müller,Süd</code>
                                <?php else: ?>
                                <code>Anrede,Vorname,Nachname<br>Herr,,Meier<br>Frau,Lisa,</code>
                                <?php endif; ?>

                                <?php if($hasForeignKeys): ?>
                                <p class="mt-2"><small>Hinweis: Bei Fremdschlüssel-Feldern werden die Eingaben automatisch in IDs umgewandelt, wenn sie eindeutig zugeordnet werden können.</small></p>
                                <?php endif; ?>
                            </ol>
                        </div>
                    </div>

                    <!-- Import Form -->
                    <div class="form-group">
                        <div class="textarea-container">
                            <textarea id="line-numbers" class="line-numbers" readonly>1</textarea>
                            <textarea id="importData" class="form-control" rows="10" 
                                    placeholder="z.B.:&#10;Name,Alter,Stadt&#10;Max Müller,42,Berlin&#10;'Mustermann, Peter',23,Hamburg"></textarea>
                        </div>
                    </div>
                    
                    <div id="validationResult" class="alert" style="display:none;"></div>
                    <div class="mb-3">  <!-- Hier neues div mit margin-bottom -->
                        <button onclick="validateImport()" class="btn btn-primary">Daten prüfen</button>
                        <button onclick="validateImport(true)" class="btn btn-success ml-2" id="importButton" style="display:none;">Daten importieren</button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
