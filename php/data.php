<?php
$admin = 1;
$tabelle = "sparten";
$tabelle_upper = strtoupper($tabelle)
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$tabelle_upper?> bearbeiten</title>

    <?php
    require_once(__DIR__ . "/../inc/include.php");
    
    $data = $db->query("SELECT * FROM $tabelle");
    if (!$data) $db->log(__FILE__.":".__LINE__." - ". $db->error);
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
                value: value
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

                    if (input) {
                        if (dbRow[field] == input.value.trim()) {
                            td.style.backgroundColor = 'lightgreen';
                        } else {
                            td.style.backgroundColor = 'lightcoral';
                            input.value = dbRow[field];
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
    </script>
</head>
<body>

<?php
// Manche Spalten sind per ID via Fremdschlüssel zu anderen Tabellen verknüpft. Die ID anzuzeigen (und zu bearbeiten) 
// bringt dem Anwender wenig. Es muss daher der unbequeme Weg gegangen werden, die FK 8foreign keys) zu erkennen und
// die Daten zu parsen, um den generischen Ansatz weiter verfolgen zu können. Die schema-Tabellen sind leider recht
// unzuverlässig (eigene Erfahrung). Daher wird hier der CREATE TABLE-String der Tabelle ausgelesen und die Fremdschlüssel
// per Regex ermittelt. Die Fremdschlüssel werden in einem Array gespeichert, um später die Anzeige zu verbessern.

$createTable = $db->query("SHOW CREATE TABLE $tabelle;");
$createTable = $createTable[0]['Create Table']; // Der eigentliche CREATE inkl. Fremdschlüssel
$foreignKeys = []; // Array für Fremdschlüssel

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
function renderTableHeaders($data) {
    if (!empty($data)) {
        foreach (array_keys($data[0]) as $header) {
            if (strcasecmp($header, 'id') !== 0) {
                echo '<th>' . htmlspecialchars($header) . '</th>';
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
                // Prüfen, ob es sich um eine Fremdschlüsselspalte handelt, wenn ja: vorbereitete Anzeige übernehmen.
                $data_fk_ID_key = "";
                $data_fk_ID_value = "";
                if(isset($foreignKeys[$key])){ 
                    $data_fk_ID_key = $foreignKeys[$key]['FKspalte'];
                    $data_fk_ID_value = $value;

                    // Erzeuge eine Select-Box für Fremdschlüssel-Felder, wenn Admin
                    if ($admin) {
                        echo '<select class="form-control" onchange="updateField(\'' . $tabelle . '\', \'' . $row['id'] . '\', \'' . $key . '\', this.value)">';
                        foreach ($foreignKeys[$key]['anzeige'] as $fk_value => $fk_display) {
                            $selected = ($fk_value == $value) ? 'selected' : '';
                            echo '<option value="' . htmlspecialchars($fk_value) . '" ' . $selected . '>' . htmlspecialchars($fk_display) . '</option>';
                        }
                        echo '</select>';
                    } else {
                        echo htmlspecialchars($foreignKeys[$key]['anzeige'][$value]);
                    }
                } else {
                    $value = $row[$key];
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
    <h2><?=$tabelle_upper?><h2>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <?php renderTableHeaders($data); ?>
                </tr>
            </thead>
            <tbody>
                <?php renderTableRows($data, $admin, $tabelle, $foreignKeys); ?>
            </tbody>
        </table>
    </div>

</body>
</html>
