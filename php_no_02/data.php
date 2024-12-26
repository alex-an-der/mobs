<?php
$admin = 1;
if(isset($_GET['tab'])) {
    $tabelle = $_GET['tab'];
} else {
    $tabelle = "";
}

$tabelle_upper = strtoupper($tabelle)
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=!empty($tabelle_upper) ? $tabelle_upper.' bearbeiten' : 'Tabelle ausw&auml;hlen'?></title>

    <?php
    require_once(__DIR__ . "/../inc/include.php");
    
    $data = [];
    if (!empty($tabelle)) {
        // Find the auto-increment column
        $columns = $db->query("SHOW COLUMNS FROM $tabelle");
        $autoIncrementColumn = null;
        foreach ($columns as $column) {
            if ($column['Extra'] === 'auto_increment') {
                $autoIncrementColumn = $column['Field'];
                break;
            }
        }

        if ($autoIncrementColumn) {
            $data = $db->query("SELECT * FROM $tabelle ORDER BY $autoIncrementColumn DESC");
        } else {
            $data = $db->query("SELECT * FROM $tabelle");
        }

        if (!$data) $db->log(__FILE__.":".__LINE__." - ". $db->error);
    }
    ?>

    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .form-control.border-0 {
            background-color:rgba(0,0,0,0) !important;
        }
        .form-control.border-0:focus {
            background-color:rgba(0,0,0,0) !important;
        }
        .highlight-new {
            background-color: lightyellow !important;
        }
        .error-cell {
            background-color: red !important;
        }
        .toggle-btn {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .toggle-btn-header {
            color: darkgrey !important;
        }
    </style>

    <script>
        function updateField(tabelle, id, field, value) {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "ajax.php", true);
            xhr.setRequestHeader("Content-Type", "application/json");

            const data = JSON.stringify({
                action: 'update',
                tabelle: tabelle,
                id: id,
                field: field,
                value: value === "" ? null : value  // Sende NULL, wenn der Wert leer ist
            });

            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.status === "success") {
                            checkRow(tabelle, id, field, value);
                        } else {
                            markCellError(id, field);
                            alert("Fehler beim Update. Stimmt das Datenformat?");
                        }
                    } catch (e) {
                        markCellError(id, field);
                        alert("Fehler beim Verarbeiten der Serverantwort.");
                    }
                } else if (xhr.readyState === 4 && xhr.status !== 200) {
                    markCellError(id, field);
                    alert("Serverfehler beim Update. Stimmt das Datenformat?");
                }
            };

            xhr.send(data);
        }

        function checkRow(tabelle, id, field, value) {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "ajax.php", true);
            xhr.setRequestHeader("Content-Type", "application/json");

            const data = JSON.stringify({
                action: 'check',
                tabelle: tabelle,
                id: id,
                field: field,
                value: value
            });

            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.status === "success" && response.row) {
                            updateRowColors(response.row, id, field);
                        }
                    } catch (e) {
                        alert("Fehler beim Abgleich der Zeile.");
                    }
                }
            };

            xhr.send(data);
        }

        function updateRowColors(dbRow, id, field) {
            const row = document.querySelector(`tr[data-id='${id}']`);
            if (row) {
                const td = row.querySelector(`td[data-field='${field}']`);
                if (td) {
                    const input = td.querySelector('input');
                    const select = td.querySelector('select');

                    if (input) {
                        const inputValue = input.value.trim();
                        const dbValue = dbRow[field] === null ? "" : dbRow[field].toString().trim();

                        const inputNumber = parseFloat(inputValue.replace(',', '.'));
                        const dbNumber = parseFloat(dbValue.replace(',', '.'));

                        if ((!isNaN(inputNumber) && !isNaN(dbNumber) && inputNumber === dbNumber) || inputValue === dbValue) {
                            td.style.backgroundColor = 'lightgreen';
                        } else {
                            td.style.backgroundColor = 'lightcoral';
                            input.value = dbRow[field];
                        }
                    }

                    if (select) {
                        const dbValue = dbRow[field] === null ? "" : dbRow[field];  // NULL wird zu ""
                        if (dbValue == select.value) {
                            td.style.backgroundColor = 'lightgreen';
                        } else {
                            td.style.backgroundColor = 'lightcoral';
                            select.value = dbValue;
                        }
                    }
                }
            }
        }

        function markCellError(id, field) {
            const row = document.querySelector(`tr[data-id='${id}']`);
            if (row) {
                const td = row.querySelector(`td[data-field='${field}']`);
                if (td) {
                    td.classList.add('error-cell');
                }
            }
        }

        function clearCellColor(input) {
            const td = input.closest('td');
            if (td) {
                td.style.backgroundColor = '';
                td.classList.remove('error-cell');
            }
        }

        function sortTable(column, order) {
            const table = document.querySelector('table tbody');
            const rows = Array.from(table.rows);

            rows.sort((a, b) => {
                const aCell = a.querySelector(`td[data-field='${column}']`);
                const bCell = b.querySelector(`td[data-field='${column}']`);

                let aText = aCell.innerText.trim();
                let bText = bCell.innerText.trim();

                const aInput = aCell.querySelector('input, select');
                const bInput = b.querySelector('input, select');

                if (aInput) aText = aInput.value.trim();
                if (bInput) bText = bInput.value.trim();

                const aNumber = parseFloat(aText.replace(',', '.'));
                const bNumber = parseFloat(bText.replace(',', '.'));

                if (!isNaN(aNumber) && !isNaN(bNumber)) {
                    return order === 'asc' ? aNumber - bNumber : bNumber - aNumber;
                } else {
                    return order === 'asc' ? aText.localeCompare(bText, undefined, { numeric: true }) : bText.localeCompare(aText, undefined, { numeric: true });
                }
            });

            rows.forEach(row => table.appendChild(row));
        }

        function addSortEventListeners() {
            const headers = document.querySelectorAll('table thead th');
            headers.forEach(header => {
                header.addEventListener('click', () => {
                    const column = header.getAttribute('data-field');
                    const currentOrder = header.getAttribute('data-order') || 'asc';
                    const newOrder = currentOrder === 'asc' ? 'desc' : 'asc';

                    sortTable(column, newOrder);
                    header.setAttribute('data-order', newOrder);
                });
            });
        }

        function filterTable() {
            const filterInput = document.getElementById('tableFilter');
            if (!filterInput) return;

            const filterValue = filterInput.value.toLowerCase();
            const rows = document.querySelectorAll('table tbody tr');

            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                let match = false;

                cells.forEach(cell => {
                    const input = cell.querySelector('input');
                    const select = cell.querySelector('select');
                    let cellValue = cell.textContent.toLowerCase();

                    if (input) {
                        cellValue = input.value.toLowerCase();
                    } else if (select) {
                        cellValue = select.options[select.selectedIndex].text.toLowerCase();
                    }

                    if (cellValue.includes(filterValue)) {
                        match = true;
                        if (filterValue) {
                            cell.style.backgroundColor = '#D3D9F2';
                        } else {
                            cell.style.backgroundColor = '';
                        }
                    } else {
                        cell.style.backgroundColor = '';
                    }
                });

                if (match) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function insertDefaultRecord(tabelle) {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "ajax.php", true);
            xhr.setRequestHeader("Content-Type", "application/json");

            const data = JSON.stringify({
                action: 'insert_default',
                tabelle: tabelle
            });

            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.status === "success") {
                            location.reload(); // Reload the page to see the new record
                        } else {
                            alert("Fehler beim Einfügen des Datensatzes. Bitte prüfen Sie die log-Tabelle in der Datenbank!");
                        }
                    } catch (e) {
                        alert("Fehler beim Verarbeiten der Serverantwort.");
                    }
                } else if (xhr.readyState === 4 && xhr.status !== 200) {
                    alert("Serverfehler beim Einfügen des Datensatzes. Bitte prüfen Sie die log-Tabelle in der Datenbank!");
                }
            };

            xhr.send(data);
        }

        function toggleSelectAll(source) {
            const buttons = document.querySelectorAll('.toggle-btn');
            buttons.forEach(button => {
                if (source.classList.contains('btn-outline-secondary')) {
                    button.classList.add('btn-light');
                    button.classList.remove('btn-outline-light');
                } else {
                    button.classList.remove('btn-light');
                    button.classList.add('btn-outline-light');
                }
            });
            source.classList.toggle('btn-outline-secondary');
            source.classList.toggle('btn-secondary');
        }

        function toggleRowSelection(button) {
            if (button.classList.contains('btn-outline-light')) {
                button.classList.add('btn-light');
                button.classList.remove('btn-outline-light');
                button.innerText = 'X';
            } else {
                button.classList.remove('btn-light');
                button.classList.add('btn-outline-light');
                button.innerText = 'X';
            }
        }

        function deleteSelectedRows(tabelle) {
            const selectedIds = Array.from(document.querySelectorAll('.toggle-btn.btn-light')).map(btn => btn.getAttribute('data-id'));
            if (selectedIds.length === 0) {
                alert('Keine Zeilen ausgewählt.');
                return;
            }

            const confirmation = confirm('Sind Sie sicher, dass Sie die ausgewählten Daten löschen möchten?');
            if (!confirmation) return;

            const xhr = new XMLHttpRequest();
            xhr.open("POST", "ajax.php", true);
            xhr.setRequestHeader("Content-Type", "application/json");

            const data = JSON.stringify({
                action: 'delete',
                tabelle: tabelle,
                ids: selectedIds
            });

            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.status === "success") {
                            location.reload(); // Reload the page to see the changes
                        } else {
                            alert("Fehler beim Löschen der Daten.");
                        }
                    } catch (e) {
                        alert("Fehler beim Verarbeiten der Serverantwort.");
                    }
                } else if (xhr.readyState === 4 && xhr.status !== 200) {
                    alert("Serverfehler beim Löschen der Daten.");
                }
            };

            xhr.send(data);
        }

        document.addEventListener('DOMContentLoaded', () => {
            addSortEventListeners();
            const filterInput = document.getElementById('tableFilter');
            if (filterInput) {
                filterInput.addEventListener('input', filterTable);
                filterInput.value = ''; // Clear filter field on page load
            }
            const insertButton = document.getElementById('insertDefaultButton');
            if (insertButton) {
                insertButton.addEventListener('click', function() {
                    insertDefaultRecord('<?=$tabelle?>');
                });
            }
            const deleteButton = document.getElementById('deleteSelectedButton');
            if (deleteButton) {
                deleteButton.addEventListener('click', function() {
                    deleteSelectedRows('<?=$tabelle?>');
                });
            }
        });

        function formatAndUpdateField(tabelle, id, field, value, decimalPlaces) {
            if (!isNaN(value) && value !== "") {
                value = parseFloat(value).toFixed(decimalPlaces);
            }
            updateField(tabelle, id, field, value);
        }
    </script>
</head>
<body>
 
<?php
// Manche Spalten sind per ID via Fremdschlüssel zu anderen Tabellen verknüpft. Die ID anzuzeigen (und zu bearbeiten) 
// bringt dem Anwender wenig. Es muss daher der unbequeme Weg gegangen werden, die FK 8foreign keys) zu erkennen und
// die Daten zu parsen, um den generischen Ansatz weiter verfolgen zu können. Die schema-Tabellen sind leider recht
// unzuverlässig (eigene Erfahrung). Daher wird hier der CREATE TABLE-String der Tabelle ausgelesen und die Fremdschlüssel
// per Regex ermittelt. Die Fremdschlüssel werden in einem Array gespeichert, um später die Anzeige zu verbessern.


$createTable = $createTable[0]['Create Table']; // Der eigentliche CREATE inkl. Fremdschlüssel
$foreignKeys = []; // Array für Fremdschlüssel
if (!empty($tabelle)) {
    $createTable = $db->query("SHOW CREATE TABLE $tabelle;");
    $createTable = $createTable[0]['Create Table'];
}

// Regex zur Erkennung von Fremdschlüsseln mit FK-Name
$pattern = "/CONSTRAINT `([^`]+)` FOREIGN KEY \\(`([^`]+)`\\) REFERENCES `([^`]+)` \\(`([^`]+)`\\)/";

// Suche nach allen Fremdschlüsseldefinitionen
if (preg_match_all($pattern, $createTable, $matches, PREG_SET_ORDER)) {
    foreach ($matches as $match) {
        
        $FKname = $match[1];
        $FKtabelle = $match[3];
        $FKspalte = $match[4];
        $SRCspalte = $match[2];
        
        $darstellungspattern = constant($FKname);
        if(!$darstellungspattern) $db->log(__FILE__.":".__LINE__." - Die benötigte Konstante $FKname zur Darstellung einer Fremdschlüsselverknüpfung wurde in config.php nicht gesetzt");
        
        preg_match_all('/##(.*?)##/', $darstellungspattern, $matches);
        
        // Extrahierte Spalten in ein Array speichern
        $anzuzeigendeSpaltenArray = $matches[1];
            
        // Hole die zugehörigen Daten jetzt in einem Rutsch aus der Datenbank
        $FKdata_all = $db->query("SELECT * FROM $FKtabelle");
        $FKdata = array();

        // gehe die verknüpfte Tabelle zeilenweise durch und hole die Werte gemäß des Anzeigepatterns
        foreach($FKdata_all as $row){
            $anzeige = $darstellungspattern; // Anzeige-Template
            foreach($anzuzeigendeSpaltenArray as $anzuzeigendeSpalte){
                $anzeige = str_replace("##$anzuzeigendeSpalte##", $row[$anzuzeigendeSpalte], $anzeige);
            }
            $FKdata[$row['id']] = $anzeige;
            
        }
    
        $foreignKeys[$SRCspalte] = [
            'FKspalte' => $FKspalte,
            'anzeige' => $FKdata
        ]; 
    }
}


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function renderTableSelectBox($db) {
    $selectedTable = isset($_GET['tab']) ? $_GET['tab'] : "";
    $tables = $db->query("SELECT TABLE_NAME, TABLE_COMMENT FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME");

    echo '<p><form action="data.php" method="get">';
    echo '<select name="tab" class="form-control" onchange="this.form.submit()">';

    // Nur anzeigen, wenn keine Tabelle ausgewählt ist
    if (empty($selectedTable)) {
        echo '<option value="">-- Tabelle wählen --</option>';
    }

    foreach ($tables as $table) {
        $tableName = htmlspecialchars($table['TABLE_NAME']);
        $tableComment = htmlspecialchars($table['TABLE_COMMENT']);
        $displayText = !empty($tableComment) ? "$tableComment" : $tableName;
        $selected = ($tableName === $selectedTable) ? 'selected' : '';

        if (!in_array($tableName, NOSHOWS)) {
            echo '<option value="' . $tableName . '" ' . $selected . '>' . $displayText . '</option>';
        }
    }

    echo '</select>';
    echo '</form></p>';
}


function renderTableHeaders($data) {
    if (!empty($data)) {
        echo '<th><button type="button" class="btn btn-outline-secondary btn-sm toggle-btn toggle-btn-header" id="selectAll" onclick="toggleSelectAll(this)">X</button></th>'; // Toggle button for selecting all rows
        foreach (array_keys($data[0]) as $header) {
            if (strcasecmp($header, 'id') !== 0) {
                echo '<th data-field="' . htmlspecialchars($header) . '">' . htmlspecialchars($header) . '</th>';
            }
        }
    }
}

function renderTableRows($data, $admin, $tabelle, $foreignKeys) {
    global $db;
    $columns = $db->query("SHOW COLUMNS FROM $tabelle");
    $columnTypes = [];
    foreach ($columns as $column) {
        $columnTypes[$column['Field']] = $column['Type'];
    }

    foreach ($data as $row) {
        echo '<tr data-id="' . $row['id'] . '">';
        echo '<td><button type="button" class="btn btn-outline-light btn-sm toggle-btn" data-id="' . $row['id'] . '" onclick="toggleRowSelection(this)">X</button></td>'; // Toggle button for each row
        foreach ($row as $key => $value) {
            if (strcasecmp($key, 'id') !== 0) {
                echo '<td data-field="' . $key . '">';
                $data_fk_ID_key = "";
                $data_fk_ID_value = "";
                
                if(isset($foreignKeys[$key])) { 
                    $data_fk_ID_key = $foreignKeys[$key]['FKspalte'];
                    $data_fk_ID_value = $value;

                    if ($admin) {
                        echo '<select class="form-control border-0" style="background-color: inherit;" onchange="updateField(\'' . $tabelle . '\', \'' . $row['id'] . '\', \'' . $key . '\', this.value)">';
                        echo '<option value=""' . (empty($value) ? ' selected' : '') . '>---</option>';  // Leere Option
                        foreach ($foreignKeys[$key]['anzeige'] as $fk_value => $fk_display) {
                            $selected = ($fk_value == $value) ? 'selected' : '';
                            echo '<option value="' . htmlspecialchars($fk_value) . '" ' . $selected . '>' . htmlspecialchars($fk_display) . '</option>';
                        }
                        echo '</select>';
                    } else {
                        echo htmlspecialchars($foreignKeys[$key]['anzeige'][$value]);
                    }
                } else {
                    if ($admin) {
                        $inputType = 'text';
                        $columnType = $columnTypes[$key];
                        if (strpos($columnType, 'int') !== false) {
                            $inputType = 'number';
                        } elseif (preg_match('/decimal\((\d+),(\d+)\)/', $columnType, $matches) || preg_match('/float\((\d+),(\d+)\)/', $columnType, $matches)) {
                            $inputType = 'number';
                            $decimalPlaces = (int)$matches[2];
                            $value = number_format((float)$value, $decimalPlaces, '.', '');
                        } elseif (strpos($columnType, 'date') !== false) {
                            if (strpos($columnType, 'datetime') !== false) {
                                $inputType = 'datetime-local';
                                $value = str_replace(' ', 'T', $value);
                            } else {
                                $inputType = 'date';
                            }
                        }
                        echo '<input data-fkIDkey="' . htmlspecialchars($data_fk_ID_key) . '" data-fkIDvalue="' . htmlspecialchars($data_fk_ID_value) . '" type="' . $inputType . '" class="form-control border-0" style="background-color: inherit;" value="' . htmlspecialchars($value) . '"
                              onchange="formatAndUpdateField(\'' . $tabelle . '\', \'' . $row['id'] . '\', \'' . $key . '\', this.value, ' . $decimalPlaces . ')"
                              onfocus="clearCellColor(this)">';
                    } else {
                        if (preg_match('/decimal\((\d+),(\d+)\)/', $columnType, $matches) || preg_match('/float\((\d+),(\d+)\)/', $columnType, $matches)) {
                            $decimalPlaces = (int)$matches[2];
                            $value = number_format((float)$value, $decimalPlaces, '.', '');
                        }
                        echo htmlspecialchars($value);
                    }
                }
                echo '</td>';
            }
        }
        echo '</tr>';
    }
}
?>

    <div class="container mt-4">
    <!--h2><?=$tabelle_upper?><h2-->
    <div class="container mt-4" style="font-size: 1.75rem; font-weight: bold;">
        <?php renderTableSelectBox($db); ?>
    </div>

    <div class="container mt-2">
        <p><input type="text" id="tableFilter" class="form-control" placeholder="Filter..."></p>
    </div>

    <?php if (!empty($tabelle) && $admin): ?>
    <div class="container mt-2">
        <button id="insertDefaultButton" class="btn btn-primary mb-2">Neuen Datensatz einfügen</button>
        <button id="deleteSelectedButton" class="btn btn-danger mb-2">Ausgewählte Zeilen löschen</button>
    </div>
    <?php endif; ?>

    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <?php renderTableHeaders($data); ?>
            </tr>
        </thead>
        <tbody>
        <?php 
            if (!empty($data)) renderTableRows($data, $admin, $tabelle, $foreignKeys);
        ?>
        </tbody>
    </table>
</div>

</body>
</html>

