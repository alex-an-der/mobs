<?php // Ganz oben wegen session_start(). Auch kein <!DOCTYPE html> vorher!
ob_start();
require_once(__DIR__ . "/user_includes/all.head.php");
require_once(__DIR__ . "/user_includes/index.head.php");
require_once(__DIR__ . "/inc/include.php");
ob_end_flush();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <!-- Favicon and mobile web app settings -->
    <link rel="icon" href="./inc/img/mobs.jpg" type="image/jpeg">
    <link rel="apple-touch-icon" href="./inc/img/mobs.jpg">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="<?=TITEL?>">
    <!-- For Android devices -->
    <link rel="manifest" href="./manifest.json">
    <meta name="theme-color" content="#ffffff">

<?php
$readwrite = 0;
$deleteAnyway = 0;
$selectedTableID = isset($_GET['tab']) ? $_GET['tab'] : "";
$data = array();
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

    // Schreibrechte?
    if(isset($anzuzeigendeDaten[$selectedTableID]['writeaccess'])){
        $readwrite = $anzuzeigendeDaten[$selectedTableID]['writeaccess'];
    }else{
        $readwrite = 0;
    }

    // Importrechte?
    if(isset($anzuzeigendeDaten[$selectedTableID]['import'])){
        $importErlaubt = $anzuzeigendeDaten[$selectedTableID]['import'];
    }else{
        $importErlaubt = 0;
    }

    // Ausnahmsweise Delete-Rechte?
    if(isset($anzuzeigendeDaten[$selectedTableID]['deleteanyway'])){
        $deleteAnyway = $anzuzeigendeDaten[$selectedTableID]['deleteanyway'];
    }else{
        $deleteAnyway = 0;
    }
    
    // Query funktioniert?
    $data = $db->query($dataquery);
    if(isset($data['error'])){
        $err = "<p>Die Konstante \$anzuzeigendeDaten[$selectedTableID]['query'] enth&auml;lt keinen g&uuml;ltiges SQL-Query:</p> <p><b>". $data['error']."</b></p>";
        dieWithError($err,__FILE__,__LINE__);
    } elseif (isset($data['message'])) {
        // Leerer Datensatz
        $err = $data['message'];
        $data = array();
    } elseif (isset($data['data'])) {
        // Datensätze vorhanden
        $data = $data['data'];

        // Gibt es eine ID-Spalte?
        if(!isset($data[0]['id'])){
            $err = "Die Konstante \$anzuzeigendeDaten[$selectedTableID]['query'] muss eine Spalte 'id' zur&uuml;ckgeben.";
            dieWithError($err,__FILE__,__LINE__);
        }
    } else {
        // Unerwarteter Fall
        $err = "Ein unbekannter Fehler ist aufgetreten."; 
        dieWithError($err,__FILE__,__LINE__);
    }
    


} else {
    $tabelle = "";
}

$tabelle_upper = strtoupper($tabelle);
?>


    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=TITEL?></title>

    <?php ?>

    <style>
    @import url('index.css'); 
    </style>



</head>
<body>

<?php
// Manche Spalten sind per ID via Fremdschlüssel zu anderen Tabellen verknüpft. Die ID anzuzeigen (und zu bearbeiten) 
// bringt dem Anwender wenig. Es muss daher in config pro FK eine Referenzquery definiert, die die ID in eine für den
// Anwender nützliche Information umwandelt. Diese Information wird dann in einer Select-Box angezeigt. 
// Der Query muss genau zwei Dinge liefern: id (zur Verknüpfung, ist dann der Value der Option) und anzeige (der Text der Option).


/////////////////////////////////////////////////////////////////////

$FKdata = array();

if(isset($anzuzeigendeDaten[$selectedTableID]['referenzqueries'])){
    $substitutionsQueries = $anzuzeigendeDaten[$selectedTableID]['referenzqueries'];

    
    foreach($substitutionsQueries as $SRC_ID => $query){
        $FKname = '$anzeigeSubstitutionen'."['$tabelle']['$SRC_ID']";
            
            $result = $db->query($query);
            if(!isset($result['data'])){
                $result = array();
                $result['data'][0]['id'] = 0;
                $result['data'][0]['anzeige'] = "Keine Daten vorhanden";            
            }
            $FKdarstellungAll = $result['data'];

            
            if (!$FKdarstellungAll) {
                if(isset($result['error'])){
                    $err = "Die benötigte Konstante $FKname enthält kein gültiges SQL-Statement. (Eingelesener Query: $query)";
                    if(isset($result['error'])) $err .= "<p>".$result['error']."</p>";
                    dieWithError($err,__FILE__,__LINE__);
                }else{
                    continue;
                }
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
} 


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function dieWithError($err, $file, $line, $stayAlive = false) {
    global $db;
    $db->log("$file:$line - $err");
    echo("<br><div class='container'><b>Konfigurationsfehler:</b> $err</div>");
    if(!$stayAlive) die();
}

function renderTableSelectBox($db) {
    global $anzuzeigendeDaten;
    global $selectedTableID;
    echo '<div class="table-select-wrapper mb-3">';
    echo '<form method="get" class="table-select-form">';
    // Zeile: Tabellenauswahl und ?-Button
    echo '<div class="table-select-row">';
    echo '<select id="tableSelectBox" name="tab" class="form-control table-select-box" onchange="this.form.submit()">';
    if(!isset($anzuzeigendeDaten[$selectedTableID])){
        echo '<option value="">-- Tabelle wählen --</option>';
    }
    $trennerindizies = array();
    $options = array();
    foreach ($anzuzeigendeDaten as $index => $table) {
        if(isset($table['trenner'])){
            $trennerindizies[] = count($options);
            $options[] = $table['trenner'];
        }else{
            $tableName = htmlspecialchars($table['tabellenname']);
            $tableComment = htmlspecialchars($table['auswahltext']);
            $displayText = !empty($tableComment) ? "$tableComment" : $tableName;
            $selected = ($index == $selectedTableID) ? 'selected' : '';
            $options[] = '<option  value="' . $index . '" ' . $selected . '>' . $displayText . '</option>';
        }
    }
    foreach ($trennerindizies as $trennerindex) {
        $options[$trennerindex] = '<option disabled>-----</option>';
    }
    echo implode("\n", $options);
    echo '</select>';
    // ?-Button direkt daneben
    echo '<a href="doc/hilfe.html" target="_blank" class="btn btn-secondary table-help-btn ms-2">?</a>';
    echo '</div>';
    echo '</form>';
    echo '</div>';
}

function hatUserBerechtigungen(){
    # Das sieht man, wenn die FK-Spalten etwas zurückliefern. FK-Select macht ein neuer Datensatz keinen Sinn.
    global $anzuzeigendeDaten;
    global $selectedTableID;
    global $db;
    
    if(isset($anzuzeigendeDaten[$selectedTableID]['referenzqueries'])){
        $substitutionsQueries = $anzuzeigendeDaten[$selectedTableID]['referenzqueries'];
        
        foreach($substitutionsQueries as $SRC_ID => $query){
            $result = $db->query($query);
            if(isset($result['data'])) {
                return true;
            }
        }
    }
    return false;
}

function renderTableHeaders($data) { 
    global $anzuzeigendeDaten;
    global $selectedTableID;
    global $importErlaubt;
    global $deleteAnyway;
    
    if (!empty($data)) {
        if($importErlaubt || $deleteAnyway)
            echo "<th style='width: 60px'><div class='checkbox-header-container p-2'><input type='checkbox' class='form-check-input' onclick='toggleSelectAll(this)'></div></th>"; // Checkbox for selecting all rows
        foreach (array_keys($data[0]) as $header) {
            $style = "";
            if(isset($anzuzeigendeDaten[$selectedTableID]['spaltenbreiten'][$header])) {
                $style = "style='width: ".$anzuzeigendeDaten[$selectedTableID]['spaltenbreiten'][$header]."px;'";
            }
            if (strcasecmp($header, 'id') !== 0) {
                $displayHeader = $header;
                if (strpos($header, 'info:') === 0) {
                    $displayHeader = substr($header, 5); // Remove 'info:' prefix
                }
                if (strpos($header, 'ajax:') === 0) {
                    $displayHeader = substr($header, 5); // Remove 'ajax:' prefix 
                }
                echo "<th $style data-field='" . htmlspecialchars($header) . "'>" . htmlspecialchars($displayHeader) . "</th>";
            }
        }
        
        // Die Zeile mit den Datentyp-Anzeigen wird komplett entfernt
        
        // Filterzeile einfügen (bestehender Code)
        echo "</tr><tr id='columnFilters'>";
        if($importErlaubt || $deleteAnyway) echo "<th></th>"; // Leeres Feld für Checkbox
        foreach (array_keys($data[0]) as $header) {
            if (strcasecmp($header, 'id') !== 0) {
                $fieldName = $header;
                // Anzeige ohne Prefix, aber data-field mit Prefix
                $displayHeader = $header;
                if (strpos($header, 'info:') === 0) {
                    $displayHeader = substr($header, 5);
                }
                if (strpos($header, 'ajax:') === 0) {
                    $displayHeader = substr($header, 5);
                }
                echo "<th><input type='text' class='form-control form-control-sm column-filter' data-field='" . htmlspecialchars($header) . "' placeholder='Filter...'></th>";
            }
        }
    } else {
        if ($selectedTableID !== "") {
            echo "<div class='container mt-4'><div class='alert alert-light' role='alert'>Diese Liste ist noch leer.</div></div>";
        } else {
            echo "<style>::-webkit-scrollbar { display: none; }</style><div class='container mt-4' style='overflow-y: hidden;'><div class='alert alert-light' role='alert'>Bitte wählen Sie eine Tabelle aus.</div></div>";
        }
    }
}

function renderTableRows($data, $tabelle, $foreignKeys) {
    global $db;
    global $anzuzeigendeDaten;
    global $selectedTableID;
    global $readwrite;
    global $deleteAnyway;
    global $importErlaubt;
    

    $columns = $db->query("SHOW COLUMNS FROM $tabelle"); 
    $columnTypes = [];
    $columnMayBeNULL = [];
    foreach ($columns['data'] as $column) {
        $columnTypes[$column['Field']] = $column['Type'];
        $columnMayBeNULL[$column['Field']] = ($column['Null'] === 'YES') ? true : false;
    }

    foreach ($data as $row) {
        
        echo '<tr data-id="' . $row['id'] . '">';
        if($importErlaubt || $deleteAnyway)
            echo '<td><div class="checkbox-container"><input type="checkbox" class="form-check-input row-checkbox" data-id="' . $row['id'] . '" onclick="toggleRowSelection(this)"></div></td>';
        
        foreach ($row as $key => $value) {
            if ($value === null) {
                $value = "";
            }
            if (strcasecmp($key, 'id') !== 0) {
                $style = "style='";
                if(isset($anzuzeigendeDaten[$selectedTableID]['spaltenbreiten'][$key])) {
                    $style .= "width: ".$anzuzeigendeDaten[$selectedTableID]['spaltenbreiten'][$key]."px;";
                }
                $style .= "word-wrap: break-word; white-space: normal;'";
                
                // Prefix entfernen für data-field
                $dataFieldKey = $key;
                // Anzeige ohne Prefix, aber data-field mit Prefix
                $displayValue = $value;
                $isInfoColumn = strpos($key, 'info:') === 0;
                $isAjaxColumn = strpos($key, 'ajax:') === 0;
                if ($isInfoColumn) {
                    $displayValue = $value; // ggf. anpassen, falls du die Anzeige ändern willst
                }
                if ($isAjaxColumn) {
                    $displayValue = $value; // ggf. anpassen
                }
                echo '<td data-field="' . htmlspecialchars((string)$key) . '" ' . $style . '>';
                $data_fk_ID_key = "";
                $data_fk_ID_value = "";

                // Info-Spalte: Wert ggf. aus Feld ohne info:-Prefix holen
                $isInfoColumn = strpos($key, 'info:') === 0;
                $isAjaxColumn = strpos($key, 'ajax:') === 0; // Immer initialisieren!
                if ($isAjaxColumn) {                                                        
                            $key = substr($key, 5);             
                            if (isset($anzuzeigendeDaten[$selectedTableID]['ajaxfile'])) {
                                $ajaxfile = $anzuzeigendeDaten[$selectedTableID]['ajaxfile'];
                            } else {
                                $ajaxfile = 'no_file_configured';
                            }
                }

                $displayValue = $value;
                
                // Unterscheidung zwischen Auswahllisten ("Foreign-Keys-Spalten") wie z.B. "BSG" und normale Text-Spalten wie z.B. "Vorname"
                if(isset($foreignKeys[$key])) { 
                    /* ************************************************************************************************
                       * AJAX: - Spalten (user-code-ajax) werden mit FK (noch) nicht unterstützt!                     *
                       * **********************************************************************************************/
                    // Hole die Anzeigedaten mit der Referenz-ID
                    
                    foreach($foreignKeys[$key] as $fk){
                        if($fk['id'] == $value){
                            $data_fk_ID_key = $fk['id'];
                            $data_fk_ID_value = $fk['anzeige'];
                            break;
                        }
                        
                    }
                    // Selects nur, wenn readwrite UND keine Info-Spalte 
                    if ($readwrite && !$isInfoColumn) {
                        // SELECT ZUSAMMENSTELLEN //
                        $selectTag = '<select oncontextmenu="filter_that(event, this, \'select\');" class="form-control border-0" data-field="' 
                            . htmlspecialchars((string)$dataFieldKey) 
                            . '" style="background-color: inherit; word-wrap: break-word; white-space: normal;" onchange="updateField(\'' 
                            . addslashes($tabelle) . '\', \''
                            . addslashes($row["id"]) . '\', \''
                            . addslashes($key) . '\', this.value, 0, \'\', this)">';
                        echo $selectTag;

                        // Nur wenn Spalte nullable ist, die "---" anbieten (und nur bei r/w + non-info-Spalten)
                        if( $columnMayBeNULL[$key]) {
                            echo '<option value="NULL"' . (empty($value) ? ' selected' : '') . '>'.NULL_WERT.'</option>';
                        }

                        // OPTION - FELDER //
                        foreach ($foreignKeys[$key] as $fk) {
                            $fk_value = $fk['id'];
                            $fk_display = htmlspecialchars((string)$fk['anzeige'], ENT_QUOTES);
                            $selected = ($fk_value == $displayValue) ? 'selected' : '';
                            echo '<option value="' . htmlspecialchars((string)$fk_value, ENT_QUOTES) . '" ' . $selected . '>' . $fk_display . '</option>';
                        }
                        echo '</select>';
                    } else {
                        //$data_fk_ID_key   = $foreignKeys[$key][0]['id'];
                        //$data_fk_ID_value = $foreignKeys[$key][0]['anzeige'];
                        $anzeige = ($data_fk_ID_value !== "" && $data_fk_ID_value !== null) ? $data_fk_ID_value : NULL_WERT;
                        echo '<div oncontextmenu="filter_that(event, this, \'div\');" data-field="' . htmlspecialchars((string)$dataFieldKey) . '" style="word-wrap: break-word; white-space: normal;">' . htmlspecialchars((string)$anzeige, ENT_QUOTES) . '</div>';
                    }
                     
                } else { // normale Textspalte 
                    if ($readwrite && !$isInfoColumn) {                        
                        $inputType = 'text';
                        
                        $columnType = $columnTypes[$key];
                        if (strpos($columnType, 'date') !== false) {
                            if (strpos($columnType, 'datetime') !== false) {
                                $inputType = 'datetime-local';
                                $displayValue = str_replace(' ', 'T', $displayValue);
                            } else {
                                $inputType = 'date';
                            }
                        }
                        $updateKey = $isInfoColumn ? substr($key, 5) : $key;
                        //echo '<input oncontextmenu="filter_that(this, \'input\');" data-type="' . htmlspecialchars((string)$columnType) . '" data-fkIDkey="' . htmlspecialchars((string)$data_fk_ID_key, ENT_QUOTES) . '" data-fkIDvalue="' . htmlspecialchars((string)$data_fk_ID_value, ENT_QUOTES) . '" data-userajax="' . htmlspecialchars($isAjaxColumn ? '1' : '0', ENT_QUOTES) . '" type="' . $inputType . '" class="form-control border-0" style="background-color: inherit; word-wrap: break-word; white-space: normal;" value="' . htmlspecialchars((string)$value, ENT_QUOTES) . '" onchange="updateField(\'' . $tabelle . '\', \'' . $row['id'] . '\', \'' . $key . '\', this.value, \'' . htmlspecialchars((string)$columnType, ENT_QUOTES) . '\')" onfocus="clearCellColor(this)">';
                        echo "<input 
                        oncontextmenu=\"filter_that(event, this, 'input');\"
                        data-type=\"" . htmlspecialchars((string)$columnType) . "\"
                        data-fkIDkey=\"" . htmlspecialchars((string)$data_fk_ID_key, ENT_QUOTES) . "\"
                        data-fkIDvalue=\"" . htmlspecialchars((string)$data_fk_ID_value, ENT_QUOTES) . "\"
                        data-userajax=\"" . htmlspecialchars($isAjaxColumn ? '1' : '0', ENT_QUOTES) . "\"
                        data-field=\"$key\"
                        type=\"" . $inputType . "\"
                        class=\"form-control border-0\"
                        style=\"background-color: inherit; word-wrap: break-word; white-space: normal;\"
                        value=\"" . htmlspecialchars((string)$value, ENT_QUOTES) . "\"
                        onchange=\"updateField('{$tabelle}', '{$row['id']}', '{$key}', this.value, '" . htmlspecialchars((string)$columnType, ENT_QUOTES) . "', '" . ($ajaxfile ?? '') . "', this)\"
                        onfocus=\"clearCellColor(this)\"
                    >";
                    } else {
                        // Spezialbehandlung für Info-Spalten: Wenn leer, aber Plain-Feld vorhanden, dieses anzeigen
                        if ($isInfoColumn) {
                            $plainKey = substr($key, 5);
                            $anzeige = NULL_WERT;
                            if ((empty($displayValue) || $displayValue === null) && isset($row[$plainKey]) && $row[$plainKey] !== "" && $row[$plainKey] !== null) {
                                $anzeige = $row[$plainKey];
                            } elseif ($displayValue !== "" && $displayValue !== null) {
                                $anzeige = $displayValue;
                            }
                        } else {
                            if(isset($columnType)){
                                if (strpos($columnType, 'decimal') !== false || strpos($columnType, 'float') !== false) {
                                    $displayValue = number_format((float)$displayValue, 2, '.', '');
                                }
                            }
                            $anzeige = ($displayValue !== "" && $displayValue !== null) ? $displayValue : NULL_WERT;
                        }
                        echo '<div oncontextmenu="filter_that(event, this, \'div\');" data-field="' . htmlspecialchars((string)$dataFieldKey) . '" style="word-wrap: break-word; white-space: normal;">' . htmlspecialchars((string)$anzeige, ENT_QUOTES) . '</div>';
                    }
                }
                echo '</td>';
            }
        }
        echo '</tr>';
    }
}

?>

    <div class="flex-container container mt-4">
        <div class="container mt-4">
            <?php renderTableSelectBox($db); ?>
        

            <?php 
            if(isset($anzuzeigendeDaten[$selectedTableID]['hinweis'])){
                echo "<div class='alert alert-info'>";
                echo $anzuzeigendeDaten[$selectedTableID]['hinweis'];
                echo "</div>";
            }
            // Always show Filter and Export buttons if a table is selected
            if (!empty($tabelle)): ?>
                <div class="row">
                    <div class="btn-group-container">
                        <button id="resetButton" class="btn btn-primary" onclick="resetPage()">Neu laden</button>
                        
                        <?php if ($readwrite || hatUserBerechtigungen() || $deleteAnyway): 
                            $importErlaubt = true;

                            if (isset($anzuzeigendeDaten[$selectedTableID]['import']))
                                if ($anzuzeigendeDaten[$selectedTableID]['import'] === false)
                                    $importErlaubt = false;
                            
                            // Wenn keine Schreibrechte, dann auch keinen Import 
                            if(!$readwrite) $importErlaubt = false;
                        ?>
                            <?php if ($readwrite && $importErlaubt):?>
                                <button id="insertDefaultButton" class="btn btn-success">Einfügen</button>
                            <?php endif; ?> 
                            <?php if (($readwrite && $importErlaubt) || $deleteAnyway):?>
                                <button id="deleteSelectedButton" class="btn btn-danger">Ausgewählte löschen</button>
                            <?php endif; ?>  

                            <button id="check-duplicates" class="btn btn-primary">Dubletten suchen</button>
                            <?php if ($importErlaubt && $readwrite):?>
                                <a href="importeur.php?tab=<?= $selectedTableID ?>" class="btn btn-primary d-flex align-items-center justify-content-center">Daten importieren</a>
                            <?php endif; ?> 
                        <?php endif; ?>
                        
                        <!-- Export button is always shown when a table is selected -->
                        <div class="btn-group mb-2">
                            <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                Exportieren
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="exportData('pdf')">Als PDF</a></li>
                                <li><a class="dropdown-item" href="#" onclick="exportData('excel', 'Xlsx')">Als Excel</a></li>
                                <li><a class="dropdown-item" href="#" onclick="exportData('markdown')">Als Markdown</a></li>
                                <li><a class="dropdown-item" href="#" onclick="exportData('csv')">Als CSV</a></li>
                                <li><a class="dropdown-item" href="#" onclick="exportData('maillist')">Als Mail-Verteiler</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <?php if (!empty($statistik)):?>
                                    <li><a class="dropdown-item" href="statistik.php">Statistiken</a></li>
                                <?php endif;?>
                                
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif;?>

        </div>
        <div class="flex-container container table-container">
            <table  class="table table-striped table-bordered">
                <thead> 
                    <tr>
                        <?php renderTableHeaders($data); ?>
                    </tr>
                </thead>
                <tbody>
                <?php 
                    if (!empty($data)) renderTableRows($data, $tabelle, $FKdata);
                ?>
                </tbody>
            </table>
        </div>  <!-- Ende flex-container container table-container -->

    <footer class="text-center mt-5 mb-3">
        <a href="./user_code/impressum.php" target="_blank" style="color: #888; font-size: 0.95em; text-decoration: underline dotted;">Impressum und Datenschutzerklärung</a>
    </footer>

    <!-- Universal Modal (für Insert & Delete) -->
    <style>
        #insertModal .modal-dialog {
            max-width: 80vw;
        }
        #insertModal .table-responsive {
            max-width: 100%;
            overflow-x: auto;
        }
        @media (min-width: 900px) {
            #insertModal .table-responsive {
                max-width: 60vw;
                overflow-x: visible;
            }
            #insertModal .modal-dialog {
                max-width: 60vw;
            }
        }
        @media (min-width: 1200px) {
            #insertModal .modal-dialog {
                max-width: 80vw;
            }
            #insertModal .table-responsive {
                max-width: 80vw;
            }
        }
        #insertModal .table-responsive::-webkit-scrollbar {
            height: 18px;
            background: #eee;
        }
        #insertModal .table-responsive::-webkit-scrollbar-thumb {
            background: #bbb;
            border-radius: 8px;
            border: 4px solid #eee;
        }
        #insertModal .table-responsive {
            scrollbar-width: thin;
            scrollbar-color: #bbb #eee;
        }
        #insertModal .table-responsive + .delete-modal-spacer {
            height: 2.5em;
        }
        #insertModal tr.delete-modal-zebra-1 td {
            background: #f7f7f7 !important;
        }
        #insertModal tr.delete-modal-zebra-0 td {
            background: #fff !important;
        }
        #insertModal .delete-modal-th, #insertModal .delete-modal-td {
            white-space: nowrap;
            max-width: 350px;
            min-width: 60px;
            width: 1%;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        #insertModal .delete-modal-th {
            font-weight: bold;
        }
    </style>
    <div class="modal fade" id="insertModal" tabindex="-1" aria-labelledby="insertModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="insertModalLabel">Neuen Datensatz erstellen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="insertForm">
                        <!-- Felder werden dynamisch über diesen js-Funktions-Stack eingefügt: -->
                        <!-- 1. EVENT: DOMContentLoaded -->
                        <!-- 2. docReady (document.ready)-->
                        <!-- 3. insertDefaultRecord (holt die Tabelleninfos per AJAX) -->
                        <!-- 4. populateInsertModal (Modal-Management und Einfügen) -->
                    </form>
                    <div id="insertDeleteBody"></div>
                    <div class="delete-modal-spacer"></div>
                    <div id="insertErrorMsg" class="alert alert-danger d-none" role="alert"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="insertCancelButton">Abbrechen</button>
                    <button type="button" class="btn btn-primary" id="insertSaveButton" onclick="saveNewRecord()">Speichern</button>
                    <button type="button" class="btn btn-danger d-none" id="insertDeleteButton">Löschen</button>
                </div>
            </div>
        </div>
    </div>

    </body>
    <?php
    if ($selectedTableID !== '') {
        $tab = (int)$selectedTableID;
    } else {
        $tab = 0;
    }

    $spaltenfilter = [];
    $filteredByGET = false;
    foreach ($_GET as $key => $value) {
        if (preg_match('/^s\d+$/', $key)) {
            $spaltenfilter[(int)substr($key, 1)] = $value;
            $filteredByGET = true;
        }
}

    ?>

    <script>
        var php_tab             = <?=json_encode($tab)?>;
        var php_selectedTableID = <?= json_encode($selectedTableID)?>;
        var php_PLEASE_CHOOSE   = <?= json_encode(PLEASE_CHOOSE)?>;
        var php_tabelle         = <?= json_encode($tabelle)?>;
        var php_DB_ERROR        = <?= json_encode(DB_ERROR)?>;
        // var php_SRV_ERROR       = < ?= json_encode(SRV_ERROR)?>;
        var php_selectedTableID = <?= json_encode($selectedTableID)?>;
        var php_spaltenfilter   = <?= json_encode($spaltenfilter) ?>;
        var filteredByGET       = <?= json_encode($filteredByGET) ?>;

    </script>


    <script src="./index.js"></script>
    <script language="javascript" type="text/javascript" src="./user_includes/index_document_ready.js"></script>

    <style>
        /* --- Responsive Tabellen-Logik (Tabelle darf scrollen, wenn nötig) --- */
        .table-container {
            width: 100%;
            max-width: 100vw;
            overflow-x: auto;
            padding: 0;
            scrollbar-width: auto; /* normale Breite */
        }
        .table-container::-webkit-scrollbar {
            height: 16px; /* normale Breite */
            background: #eee;
        }
        .table-container::-webkit-scrollbar-thumb {
            background: #bbb;
            border-radius: 8px;
            border: 4px solid #eee;
        }
        /* Verhindere doppelte Scrollbars */
        body {
            overflow-x: hidden;
        }
        /* ...existing code... */
    </style>

    <script>
        function setResponsiveTableStage() {
    const container = document.querySelector('.table-container');
    if (!container) return;
    // Entferne alle Stufenklassen
    container.classList.remove('table-responsive-stage1', 'table-responsive-stage2', 'table-responsive-stage3');
    const table = container.querySelector('table');
    if (!table) return;
    // 1. Stufe: Fester Container, kein Umbruch
    if (container.offsetWidth >= table.scrollWidth && window.innerWidth > 900) {
        container.classList.add('table-responsive-stage1');
    } 
    // 2. Stufe: Fluid-Container, kein Umbruch
    else if (window.innerWidth > 700) {
        container.classList.add('table-responsive-stage2');
    } 
    // 3. Stufe: Fluid-Container, mit Umbruch
    else {
        container.classList.add('table-responsive-stage3');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    setResponsiveTableStage();
    window.addEventListener('resize', setResponsiveTableStage);
});
    </script>



</html>
