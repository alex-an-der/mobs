<?php 
require_once(__DIR__ . "/user_includes/all.head.php");
require_once(__DIR__ . "/user_includes/index.head.php");
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
    
    /*global $anzuzeigendeDaten, $selectedTableID;
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

    return count($matches) === 1 ? $matches[0] : null;*/
    }

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" width="device-width, initial-scale=1.0">
    <title><?=TITEL?></title>
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
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background-image: url('./inc/img/body_red.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        .error-table {
            width: 100%;
            margin-top: 15px;
            border-collapse: collapse;
        }
        .error-table th, .error-table td {
            border: 1px solid #dee2e6;
            padding: 8px;
        }
        .error-table th {
            background-color: #f8f9fa;
        }
        .error-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .error-message {
            color: #721c24;
            font-weight: bold;
        }
    </style>

    
    <script>
        // Globale Variablen für PHP-Werte 
        const validColumns = <?= json_encode($tableColumns ?? []) ?>;
        const hasForeignKeys = <?= json_encode($hasForeignKeys ?? false) ?>;
        const suchQueries = <?= json_encode($suchQueries ?? null) ?>;
        const tabelle = <?= json_encode($tabelle ?? '') ?>;

        function validateImport(insert=false) { 
            const validateButton = document.getElementById('validateButton');
            const importButton = document.getElementById('importButton');
            const originalText = insert ? importButton.innerHTML : validateButton.innerHTML;
            const button = insert ? importButton : validateButton;

            // Zeige den Spinner und deaktiviere den Button
            button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ' + (insert ? 'Importiere...' : 'Prüfe...');
            button.disabled = true;

            const textarea = document.getElementById('importData');
            const data = textarea.value.trim();
            const lines = data.split('\n');
            action = 'validate';
            if(insert){
                action = 'import';
            }
            
            if(lines.length < 2) {
                showValidationResult(false, 'Fehler: Mindestens Header und ein Datensatz erforderlich');
                button.innerHTML = originalText;
                button.disabled = false;
                return;
            }

            const header = parseCSVLine(lines[0]);
            
            // Prüfe nur ob die verwendeten Spalten gültig sind
            for(let col of header) {
                if(!validColumns.includes(col)) {
                    showValidationResult(false, 'Fehler: Ungültige Spalte im Header: ' + col + '<br>Erlaubte Spalten sind: ' + validColumns.join(', '));
                    button.innerHTML = originalText;
                    button.disabled = false;
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
                    button.innerHTML = originalText;
                    button.disabled = false;
                    return;
                }
            }

            // FK-Validierung hinzufügen
            if (hasForeignKeys || insert) {
                if (hasForeignKeys && !suchQueries) {
                    showValidationResult(false, 'Import wegen mangelnder Konfigurationseinstellungen nicht möglich');
                    button.innerHTML = originalText;
                    button.disabled = false;
                    return;
                }
                fetch('ajax.php', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: action,
                        rows: allRows,
                        suchQueries: suchQueries,
                        tabelle: tabelle
                    })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.status === 'error') {
                        if (typeof result.errors !== 'undefined' && result.errors.length > 0 && action === 'import') {
                            displayImportErrors(result, button, originalText);
                        } else {
                            showValidationResult(false, result.message);
                            button.innerHTML = originalText;
                            button.disabled = false;
                        }
                    } else {
                        if(insert){
                            showValidationResult(true, result.message);
                        }else{
                            showValidationResult(true, 'Die Daten k&ouml;nnen so importiert werden.');
                        }
                        button.innerHTML = originalText;
                        button.disabled = false;
                    }
                })
                .catch(error => {
                    showValidationResult(false, 'Fehler bei der Fremdschlüssel-Validierung: ' + error.message);
                    button.innerHTML = originalText;
                    button.disabled = false;
                });
            } else {
                // Wenn keine FK-Validierung nötig ist, direkt Erfolg melden
                showValidationResult(true, 'Datenformat ist korrekt! Der Import kann durchgeführt werden.');
                button.innerHTML = originalText;
                button.disabled = false;
            }
        }

        function displayImportErrors(result, button, originalText) {
            const totalRows = result.totalRows || allRows.length - 1; // Fallback to counting rows if server doesn't provide
            let errorItems = [];
            
            // Debug output to console to see what we're getting
            console.log("Result from server:", result);
            
            // Check if errors is a string that needs to be parsed
            if (typeof result.errors === 'string') {
                try {
                    errorItems = JSON.parse(result.errors);
                    console.log("Parsed error items:", errorItems);
                } catch (e) {
                    console.error("Failed to parse error string:", e);
                    errorItems = [{
                        row: 0,
                        data: "Unbekannt",
                        error: result.errors
                    }];
                }
            } else if (Array.isArray(result.errors)) {
                errorItems = result.errors;
            } else if (typeof result.errors === 'object' && result.errors !== null) {
                errorItems = Object.values(result.errors);
            }
            
            if (!errorItems || errorItems.length === 0) {
                // If we couldn't parse any errors, show the raw message
                showValidationResult(false, 'Fehler beim Import: ' + result.message);
                button.innerHTML = originalText;
                button.disabled = false;
                return;
            }
            
            // Identify which rows had errors
            const errorRowNumbers = errorItems.map(err => (typeof err.row === 'number' ? err.row : 0));
            
            // Generate list of valid row indices (row numbers starting from 1 after header)
            let validRows = [];
            for (let i = 1; i <= totalRows; i++) {
                if (!errorRowNumbers.includes(i)) {
                    validRows.push(i);
                }
            }
            
            // Use validRowIndices from server if available - but adjust for proper row indexing
            if (Array.isArray(result.validRowIndices)) {
                // Server uses 0-based indexing, but we need to add 1 to match our row indices
                validRows = result.validRowIndices.map(index => index + 1);
                console.log("Adjusted validRows (after adding 1):", validRows);
            }
            
            const failedRows = errorItems.length;
            const successRows = totalRows - failedRows;
            
            let message = `<p><strong>${failedRows} von ${totalRows} Datensätzen konnten nicht importiert werden.</strong></p>`;
            
            // Fehlerdetails in einer Tabelle anzeigen
            message += '<table class="error-table">';
            message += '<thead><tr><th>Zeile</th><th>Daten</th><th>Fehlermeldung</th></tr></thead><tbody>';
            
            for (let i = 0; i < errorItems.length; i++) {
                const err = errorItems[i];
                const rowNum = (typeof err.row === 'number' ? err.row : i) + 1; // +1 weil die Zeilennummerierung beim Header beginnt
                
                let dataDisplay = "Keine Daten";
                if (err.data) {
                    dataDisplay = Array.isArray(err.data) ? err.data.join(', ') : 
                                 (typeof err.data === 'object' ? JSON.stringify(err.data) : String(err.data));
                }
                
                let errorMsg = "Unbekannter Fehler";
                if (err.error) {
                    errorMsg = String(err.error)
                        .replace(/(<([^>]+)>)/gi, "") // Entferne HTML-Tags
                        .replace(/SQLSTATE\[\d+\]:/gi, "") // Entferne SQLSTATE-Codes
                        .replace(/Integrity constraint violation: \d+/gi, "Constraint verletzt:"); // Vereinfache Fehlermeldung
                }
                
                message += `<tr>`;
                message += `<td>${rowNum}</td>`;
                message += `<td>${dataDisplay}</td>`;
                message += `<td class="error-message">${errorMsg}</td>`;
                message += `</tr>`;
            }
            
            message += '</tbody></table>';
            
            // Always ask if user wants to import valid records
            if (validRows.length > 0) {
                message += `<div class="alert alert-warning mt-3">
                    <h5>Fortfahren mit gültigen Datensätzen?</h5>
                    <p>${validRows.length} Datensätze können problemlos importiert werden.</p>
                    <div class="d-flex gap-2">
                        <button id="continueImport" class="btn btn-warning">Ja, gültige Datensätze importieren</button>
                        <button id="cancelImport" class="btn btn-secondary">Abbrechen</button>
                    </div>
                </div>`;
            } else {
                message += `<div class="alert alert-danger mt-3">
                    <p>Es gibt keine gültigen Datensätze, die importiert werden könnten.</p>
                    <button id="cancelImport" class="btn btn-secondary">Zurück</button>
                </div>`;
            }
            
            showValidationResult(false, message, true);
            
            // Event-Listener für die Buttons hinzufügen
            setTimeout(() => {
                const continueButton = document.getElementById('continueImport');
                const cancelButton = document.getElementById('cancelImport');
                
                if (continueButton) {
                    continueButton.addEventListener('click', () => {
                        // Import nur der gültigen Datensätze fortsetzen
                        importPartialData(validRows);
                    });
                }
                
                if (cancelButton) {
                    cancelButton.addEventListener('click', () => {
                        // Zurück zum Bearbeitungsmodus
                        button.innerHTML = originalText;
                        button.disabled = false;
                        showValidationResult(false, 'Import abgebrochen.', false);
                    });
                }
            }, 100);
        }

        function importPartialData(validRowIndices) {
            const importButton = document.getElementById('importButton');
            importButton.disabled = true;
            importButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Importiere...';
            
            const textarea = document.getElementById('importData');
            const lines = textarea.value.trim().split('\n');
            const header = lines[0]; // Save header row
            
            console.log("Valid row indices:", validRowIndices);
            
            // Collect all non-empty rows with their indices
            const allRows = [];
            for (let i = 1; i < lines.length; i++) {
                if (lines[i].trim() === '') continue;
                allRows.push({
                    index: i,
                    data: lines[i]
                });
            }
            
            console.log("All data rows:", allRows);
            
            // Determine which rows to import - fixing the index mismatch issue
            let rowsToImport = [];
            if (validRowIndices && validRowIndices.length > 0) {
                rowsToImport = allRows.filter(row => validRowIndices.includes(row.index));
                console.log("Filtered rows to import:", rowsToImport);
            } else {
                // Otherwise assume all rows are valid
                rowsToImport = [...allRows];
            }
            
            console.log("Selected rows to import:", rowsToImport);
            
            if (rowsToImport.length === 0) {
                // Improved error message with more details
                let errorMessage = 'Keine gültigen Zeilen zum Importieren gefunden. ';
                errorMessage += `<br>validRowIndices: ${JSON.stringify(validRowIndices)}`;
                errorMessage += `<br>Anzahl gefundener Datenzeilen: ${allRows.length}`;
                errorMessage += `<br>Indizes der gefundenen Datenzeilen: ${JSON.stringify(allRows.map(r => r.index))}`;
                showValidationResult(false, errorMessage);
                importButton.disabled = false;
                importButton.innerHTML = 'Daten importieren';
                return;
            }
            
            // New approach: Import each row individually
            const totalRows = rowsToImport.length;
            let successCount = 0;
            let failureCount = 0;
            const failures = [];
            
            // Show progress message
            const resultDiv = document.getElementById('validationResult');
            resultDiv.style.display = 'block';
            resultDiv.className = 'alert alert-warning';
            resultDiv.innerHTML = `<p><strong>Import läuft...</strong></p>
                                  <div class="progress">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                         role="progressbar" style="width: 0%" 
                                         aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                                  </div>`;
            
            // Process each row sequentially with promises
            const importRow = (index) => {
                if (index >= rowsToImport.length) {
                    // All rows processed, show final result
                    finishImport();
                    return;
                }
                
                const row = rowsToImport[index];
                
                // Update progress bar
                const progressPercent = Math.round((index / rowsToImport.length) * 100);
                const progressBar = resultDiv.querySelector('.progress-bar');
                progressBar.style.width = `${progressPercent}%`;
                progressBar.setAttribute('aria-valuenow', progressPercent);
                progressBar.textContent = `${progressPercent}%`;
                
                fetch('ajax.php', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'import',
                        rows: [header, row.data], // Send only header and this one row
                        suchQueries: suchQueries,
                        tabelle: tabelle,
                        singleRowImport: true
                    })
                })
                .then(response => response.json())
                .then(result => {
                    console.log(`Row ${index + 1} import result:`, result);
                    
                    if (result.status === 'success') {
                        successCount++;
                    } else {
                        failureCount++;
                        failures.push({
                            row: row.index,
                            data: row.data,
                            error: result.message || 'Unbekannter Fehler'
                        });
                    }
                    // Process next row
                    importRow(index + 1);
                })
                .catch(error => {
                    console.error(`Error importing row ${index + 1}:`, error);
                    failureCount++;
                    failures.push({
                        row: row.index,
                        data: row.data,
                        error: error.message
                    });
                    // Process next row despite error
                    importRow(index + 1);
                });
            };
            
            // Start the import process with the first row
            importRow(0);
            
            // Function to show final results when all rows are processed
            const finishImport = () => {
                let message = '';
                
                if (successCount > 0 && failureCount === 0) {
                    // All successful
                    message = `<strong>Import erfolgreich!</strong><br>${successCount} Datensätze wurden importiert.`;
                    showValidationResult(true, message);
                } else if (successCount > 0 && failureCount > 0) {
                    // Partial success
                    message = `<p><strong>${successCount} von ${totalRows} Datensätzen wurden erfolgreich importiert.</strong></p>`;
                    
                    // Show error details for failed imports
                    message += '<p>Folgende Datensätze konnten nicht importiert werden:</p>';
                    message += '<table class="error-table">';
                    message += '<thead><tr><th>Zeile</th><th>Daten</th><th>Fehlermeldung</th></tr></thead><tbody>';
                    
                    for (const failure of failures) {
                        const errorMsg = String(failure.error)
                            .replace(/(<([^>]+)>)/gi, "")
                            .replace(/SQLSTATE\[\d+\]:/gi, "")
                            .replace(/Integrity constraint violation: \d+/gi, "Constraint verletzt:");
                        
                        message += `<tr>`;
                        message += `<td>${failure.row}</td>`;
                        message += `<td>${failure.data}</td>`;
                        message += `<td class="error-message">${errorMsg}</td>`;
                        message += `</tr>`;
                    }
                    
                    message += '</tbody></table>';
                    
                    showValidationResult(true, message); // Still show as success with warnings
                } else {
                    // All failed
                    message = `<p><strong>Fehler: Kein Datensatz konnte importiert werden.</strong></p>`;
                    
                    // Show error details
                    message += '<table class="error-table">';
                    message += '<thead><tr><th>Zeile</th><th>Daten</th><th>Fehlermeldung</th></tr></thead><tbody>';
                    
                    for (const failure of failures) {
                        const errorMsg = String(failure.error)
                            .replace(/(<([^>]+)>)/gi, "")
                            .replace(/SQLSTATE\[\d+\]:/gi, "")
                            .replace(/Integrity constraint violation: \d+/gi, "Constraint verletzt:");
                        
                        message += `<tr>`;
                        message += `<td>${failure.row}</td>`;
                        message += `<td>${failure.data}</td>`;
                        message += `<td class="error-message">${errorMsg}</td>`;
                        message += `</tr>`;
                    }
                    
                    message += '</tbody></table>';
                    
                    showValidationResult(false, message);
                }
                
                importButton.disabled = false;
                importButton.innerHTML = 'Daten importieren';
            };
        }

        function showValidationResult(isValid, message, isPartialError = false) {
            const resultDiv = document.getElementById('validationResult');
            const importButton = document.getElementById('importButton');
            const importHelpContent = document.getElementById('importHelpContent');
            
            resultDiv.style.display = 'block';
            resultDiv.className = 'alert ' + (isValid ? 'alert-success' : 'alert-danger');
            resultDiv.innerHTML = message;
            
            if (isValid) {
                importButton.style.display = 'inline-block';
                importHelpContent.classList.remove('show');
            } else if (!isPartialError) {
                importButton.style.display = 'none';
                importHelpContent.classList.add('show');
            } else {
                // Bei teilweisen Fehlern zeigen wir den Import-Button nicht,
                // da stattdessen die Bestätigungsbuttons angezeigt werden
                importButton.style.display = 'none';
            }
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
                <select name="tab" class="form-control me-2" onchange="this.form.submit()">
                    <?php /*foreach(renderTableSelectBox($db) as $option): ?>
                        <option value="<?= htmlspecialchars($option['value']) ?>" 
                                <?= $option['selected'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($option['text']) ?>
                        </option>
                    <?php endforeach; */?>
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
                            <button class="btn btn-link p-0" type="button" data-bs-toggle="collapse" data-bs-target="#importHelpContent">
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
                    <div class="mb-3">
                        <button id="validateButton" onclick="validateImport(false)" class="btn btn-primary">Daten prüfen</button>
                        <button id="importButton" onclick="validateImport(true)" class="btn btn-success ml-2" style="display:none;">Daten importieren</button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
