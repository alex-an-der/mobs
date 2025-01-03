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

    <script src="data.js"></script>

</head>
<body>
 
<?php
// Manche Spalten sind per ID via Fremdschlüssel zu anderen Tabellen verknüpft. Die ID anzuzeigen (und zu bearbeiten) 
// bringt dem Anwender wenig. Es muss daher der unbequeme Weg gegangen werden, die FK 8foreign keys) zu erkennen und
// die Daten zu parsen, um den generischen Ansatz weiter verfolgen zu können. Die schema-Tabellen sind leider recht
// unzuverlässig (eigene Erfahrung). Daher wird hier der CREATE TABLE-String der Tabelle ausgelesen und die Fremdschlüssel
// per Regex ermittelt. Die Fremdschlüssel werden in einem Array gespeichert, um später die Anzeige zu verbessern.


/////////////////////////////////////////////////////////////////////


if(isset($anzeigeSubstitutionen[$tabelle])){
    $substitutionsQueries = $anzeigeSubstitutionen[$tabelle];
}

$FKdata = array();
foreach($substitutionsQueries as $SRC_ID => $query){
    $FKname = '$anzeigeSubstitutionen'."['$tabelle']['$SRC_ID']";
        
        $FKdarstellungAll = $db->query($query);

        if (!$FKdarstellungAll) {
            $err = "Die benötigte Konstante $FKname enthält kein gültiges SQL-Statement.";
            dieWithError($err,__FILE__,__LINE__);
        } 

        if (count($FKdarstellungAll[0])!=2){
            $err = "Der Query in der Konstante $FKname muss genau zwei Ergebnisse liefern: 'id' und 'anzeige': 'id' = ID der Datensätze und 'anzeige' = ein ggf. zusammengesetzten Text, der zur Anzeige verwendet wird. Er liefert aber ".count($FKdarstellungAll[0])." Ergebnisse.";
            dieWithError($err,__FILE__,__LINE__);
        }

        if(!isset($FKdarstellungAll[0]['id'])){
            $err = "Der Query in der Konstante $FKname muss genau zwei Ergebnisse liefern: 'id' und 'anzeige'. Er liefert aber keine Daten mit der Bezeichnung 'id'.";
            dieWithError($err,__FILE__,__LINE__);
        }

        if(!isset($FKdarstellungAll[0]['anzeige'])){
            $err = "Der Query in der Konstante $FKname muss genau zwei Ergebnisse liefern: 'id' und 'anzeige'. Er liefert aber keine Daten mit der Bezeichnung 'anzeige'.";
            dieWithError($err,__FILE__,__LINE__);
        }

        foreach($FKdarstellungAll as $row){
            // ID und Anzeige - Informationen zentral sammeln
            $FKdata[$SRC_ID][] = $row;
        }
} 

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function dieWithError($err, $file, $line) {
    global $db;
    $db->log("$file:$line - $err");
    die("<br><div class='container'><b>Konfigurationsfehler:</b> $err</div>");
}

function renderTableSelectBox($db) {
    $selectedTable = isset($_GET['tab']) ? $_GET['tab'] : "";
    $tables = $db->query("SELECT TABLE_NAME, TABLE_COMMENT FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_COMMENT");

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
    $columns = $db->query("SHOW COLUMNS FROM $tabelle"); // This is where the SHOW COLUMNS query is fired
    $columnTypes = [];
    foreach ($columns as $column) {
        $columnTypes[$column['Field']] = $column['Type'];
    }

    foreach ($data as $row) {
        echo '<tr data-id="' . $row['id'] . '">';
        echo '<td><button type="button" class="btn btn-outline-light btn-sm toggle-btn" data-id="' . $row['id'] . '" onclick="toggleRowSelection(this)">X</button></td>'; // Toggle button for each row
        // Gehe alle Datensätze durch. 
        // $key = Name der Spalte, 
        // $value = der Wert, wie er in der Datenbank steht
        foreach ($row as $key => $value) {
            // id überspringen, DBI: ggf. über select-angaben direkt steuern
            if (strcasecmp($key, 'id') !== 0) {
                echo '<td data-field="' . $key . '">';
                $data_fk_ID_key = "";
                $data_fk_ID_value = "";
                
                // Gibt es zu dieser Spalte eine Substitutionsanweisung?
                if(isset($foreignKeys[$key])) { 
                    // Suche die Anzeige mit der korrekten ['id’]:
                    foreach($foreignKeys[$key] as $fk){
                        if($fk['id'] == $value){
                            $data_fk_ID_key = $fk['id'];
                            $data_fk_ID_value = $fk['anzeige'];
                            break;
                        }
                    }

                    if ($admin) {
                        echo '<select class="form-control border-0" style="background-color: inherit;" onchange="updateField(\'' . $tabelle . '\', \'' . $row['id'] . '\', \'' . $key . '\', this.value)">';
                        echo '<option value=""' . (empty($value) ? ' selected' : '') . '>---</option>';  // Leere Option
                        foreach ($foreignKeys[$key] as $fk ) {
                            $fk_value = $fk['id'];
                            $fk_display = $fk['anzeige'];

                            $selected = ($fk_value == $value) ? 'selected' : '';
                            echo '<option value="' . htmlspecialchars($fk_value) . '" ' . $selected . '>' . htmlspecialchars($fk_display) . '</option>';
                        }
                        echo '</select>';
                    } else {
                        echo htmlspecialchars($data_fk_ID_value);
                    }
                } else {
                    if ($admin) {
                        $inputType = 'text';
                        $columnType = $columnTypes[$key];
                        if (strpos($columnType, 'date') !== false) {
                            if (strpos($columnType, 'datetime') !== false) {
                                $inputType = 'datetime-local';
                                $value = str_replace(' ', 'T', $value);
                            } else {
                                $inputType = 'date';
                            }
                        }
                        echo '<input data-fkIDkey="' . htmlspecialchars($data_fk_ID_key) . '" data-fkIDvalue="' . htmlspecialchars($data_fk_ID_value) . '" type="' . $inputType . '" class="form-control border-0" style="background-color: inherit;" value="' . htmlspecialchars($value) . '"
                              onchange="updateField(\'' . $tabelle . '\', \'' . $row['id'] . '\', \'' . $key . '\', this.value)"
                              onfocus="clearCellColor(this)">';
                    } else {
                        if (strpos($columnType, 'decimal') !== false || strpos($columnType, 'float') !== false) {
                            $value = number_format((float)$value, 2, '.', '');
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
            if (!empty($data)) renderTableRows($data, $admin, $tabelle, $FKdata);
        ?>
        </tbody>
    </table>
</div>

</body>
</html>
