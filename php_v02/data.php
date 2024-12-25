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
        $data = $db->query("SELECT * FROM $tabelle");
        if (!$data) $db->log(__FILE__.":".__LINE__." - ". $db->error);
    }
 
    ?>

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
                            alert("Fehler beim Update.");
                        }
                    } catch (e) {
                        alert("Fehler beim Verarbeiten der Serverantwort.");
                    }
                } else if (xhr.readyState === 4 && xhr.status !== 200) {
                    alert("Serverfehler beim Update.");
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
                        if (dbRow[field] == input.value.trim()) {
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


        function clearCellColor(input) {
            const td = input.closest('td');
            if (td) {
                td.style.backgroundColor = '';
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
                const bInput = bCell.querySelector('input, select');

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

        document.addEventListener('DOMContentLoaded', addSortEventListeners);
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
        if(!$darstellungspattern) db->log(__FILE__.":".__LINE__." - Die benötigte Konstante $FKname zur Darstelliung einer Fremdschlüsselverknüpfung wurde in config.php nicht gesetzt");
        
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
    $tables = $db->query("SELECT TABLE_NAME, TABLE_COMMENT FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME != 'log' ORDER BY TABLE_NAME");

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

        echo '<option value="' . $tableName . '" ' . $selected . '>' . $displayText . '</option>';
    }

    echo '</select>';
    echo '</form></p>';
}


function renderTableHeaders($data) {
    if (!empty($data)) {
        foreach (array_keys($data[0]) as $header) {
            if (strcasecmp($header, 'id') !== 0) {
                echo '<th data-field="' . htmlspecialchars($header) . '">' . htmlspecialchars($header) . '</th>';
            }
        }
    }
}

function renderTableRows($data, $admin, $tabelle, $foreignKeys) {
    foreach ($data as $row) {
        echo '<tr data-id="' . $row['id'] . '">';
        foreach ($row as $key => $value) {
            if (strcasecmp($key, 'id') !== 0) {
                echo '<td data-field="' . $key . '">';
                $data_fk_ID_key = "";
                $data_fk_ID_value = "";
                
                if(isset($foreignKeys[$key])) { 
                    $data_fk_ID_key = $foreignKeys[$key]['FKspalte'];
                    $data_fk_ID_value = $value;

                    if ($admin) {
                        echo '<select class="form-control" onchange="updateField(\'' . $tabelle . '\', \'' . $row['id'] . '\', \'' . $key . '\', this.value)">';
                        echo '<option value=""' . (empty($value) ? ' selected' : '') . '>-- Kein Wert --</option>';  // Leere Option
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
                        echo '<input data-fkIDkey="' . htmlspecialchars($data_fk_ID_key) . '" data-fkIDvalue="' . htmlspecialchars($data_fk_ID_value) . '" type="text" class="form-control" value="' . htmlspecialchars($value) . '"
                              onchange="updateField(\'' . $tabelle . '\', \'' . $row['id'] . '\', \'' . $key . '\', this.value)"
                              onfocus="clearCellColor(this)">';
                    } else {
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
