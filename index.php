<?php // Ganz oben wegen session_start(). Auch kein <!DOCTYPE html> vorher!
require_once(__DIR__ . "/user_includes/all.head.php");
require_once(__DIR__ . "/user_includes/index.head.php");
require_once(__DIR__ . "/inc/include.php");
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

$tabelle_upper = strtoupper($tabelle)
?>


    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=TITEL?></title>

    <?php ?>

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
            width: 40px;
            height: 40px;
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
        thead {
            position: sticky;
        }
        .table-container {
            overflow-x: auto;
            margin: 0 auto;
        }
        .table {
            table-layout: fixed;
            margin: 0 auto;
        }
        th, td {
            white-space: nowrap;
            min-width: fit-content;
        }
        td {
            vertical-align: middle !important;
        }
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background-image: url('./inc/img/body.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        .btn-group-container {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        .btn-group-container .btn {
            flex: 1;
            margin: 5px;
        }
        .checkbox-container {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
        }
        .form-check-input {
            width: 1.5rem;
            height: 1.5rem;
            margin: 0;
            padding: 0;
        }
        .checkbox-header-container {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
        }

        /* Custom tooltip styles */
        .tooltip-inner {
            font-size: 16px; /* Larger font size for tooltip text */
            max-width: 350px; /* Wider tooltips */
            text-align: left; /* Left-aligned text */
            padding: 8px 12px; /* More padding */
        }
    </style>

    <script>


        function updateField(tabelle, id, field, value, datatype) {
            if(datatype){
                if (datatype.startsWith("decimal")) {
                    value = value.replace(',', '.'); // Replace all commas with dots
                    const match = value.match(/\d+(\.\d+)?/);
                    if (match) {
                        value = match[0];
                    } else {
                        value = ''; // Löst einen Fehler aus.
                    }
                    value = parseFloat(value);
                }
            }
            
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
                            alert("Fehler beim Update. Stimmt das Datenformat? Für Details siehe adm_log-Tabelle in der Datenbank.");
                        }
                    } catch (e) {
                        markCellError(id, field);
                        alert("Fehler beim Verarbeiten der Serverantwort.");
                    }
                } else if (xhr.readyState === 4 && xhr.status !== 200) {
                    markCellError(id, field);
                    alert("Serverfehler beim Update. Stimmt das Datenformat? Für Details siehe adm_log-Tabelle in der Datenbank.");
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
                            navigator.clipboard.writeText(input.value).then(() => {
                                alert("Die Daten konnten nicht gespeichert werden. Bitte prüfen Sie Ihre Eingabe. \n\nIhre ursprügliche Eingabe ist nicht verloren - ich habe sie in die Zwischenablage kopiert (Einfügen mit STRG & v).");
                            }).catch(err => {
                                alert("Die Daten konnten nicht gespeichert werden. Bitte prüfen Sie Ihre Eingabe."); 
                            });
                            input.value = dbRow[field];
                        }
                    }

                    if (select) {
                        const dbValue = dbRow[field] === null ? "NULL" : dbRow[field];  // NULL wird zu ""
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

        function checkDubletten() {
            const checkButton = document.getElementById('check-duplicates');
            const originalText = checkButton.innerHTML;

            // Show spinner and disable button
            checkButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Suche...';
            checkButton.disabled = true;

            fetch('ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ action: 'check_duplicates', tabelle: '<?php echo $tabelle; ?>' })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    if (data.duplicates.length > 0) {
                        filterRowsById(data.duplicates);
                        let text = 'Die Tabelle wurde gefiltert. Sie sehen nun alle Einträge, ' +
                        'die mehrfach vorhanden sind. Um wieder alle Daten anzuzeigen, laden ' +
                        'Sie die Daten neu.';
                        alert(text);
                    } else {
                        alert('Es wurden keine doppelten Einträge gefunden.');
                    }
                } else {
                    alert('Fehler beim Überprüfen auf doppelte Einträge.');
                }
            })
            .catch(error => {
                alert('Fehler beim Verarbeiten der Serverantwort.');
            })
            .finally(() => {
                // Reset button state
                checkButton.innerHTML = originalText;
                checkButton.disabled = false;
            });
        }

        function filterRowsById(desiredIds) {
            const table = document.querySelector('table'); // Passe den Selektor bei Bedarf an
            const rows = table.querySelectorAll('tr[data-id]');
            rows.forEach(row => {
                const id = parseInt(row.getAttribute('data-id'), 10); // 10 steht für das Dezimalsystem
                if (desiredIds.includes(id)) {
                    row.style.display = ''; // Zeige die Zeile an
                } else {
                    row.style.display = 'none'; // Blende die Zeile aus
                }
            });
        }

        let lastSortColumn = null;
        let lastSortOrder = 'asc';

        function sortTable(column, order) {
            const table = document.querySelector('table tbody');
            const rows = Array.from(table.rows);

            // Check if the column might contain timestamp data
            const isTimestampColumn = checkIfTimestampColumn(column);
            
            rows.sort((a, b) => {
                const aCell = a.querySelector(`td[data-field='${column}']`);
                const bCell = b.querySelector(`td[data-field='${column}']`);

                let aText, bText;

                // Get displayed text from cells
                if (aCell) {
                    const aSelect = aCell.querySelector('select');
                    const aInput = aCell.querySelector('input');
                    if (aSelect) {
                        // For select boxes, get the selected option text
                        aText = aSelect.options[aSelect.selectedIndex] ? aSelect.options[aSelect.selectedIndex].text : '';
                    } else if (aInput) {
                        aText = aInput.value;
                        // Get raw value for timestamp inputs
                        if (aInput.type === 'date' || aInput.type === 'datetime-local') {
                            aText = aInput.value;
                        }
                    } else {
                        aText = aCell.innerText;
                    }
                } else {
                    aText = '';
                }

                if (bCell) {
                    const bSelect = bCell.querySelector('select');
                    const bInput = bCell.querySelector('input');
                    if (bSelect) {
                        // For select boxes, get the selected option text
                        bText = bSelect.options[bSelect.selectedIndex] ? bSelect.options[bSelect.selectedIndex].text : '';
                    } else if (bInput) {
                        bText = bInput.value;
                        // Get raw value for timestamp inputs
                        if (bInput.type === 'date' || bInput.type === 'datetime-local') {
                            bText = bInput.value;
                        }
                    } else {
                        bText = bCell.innerText;
                    }
                } else {
                    bText = '';
                }

                // Trim the text values
                aText = aText.trim();
                bText = bText.trim();

                // Try to detect and parse timestamps
                if (isTimestampColumn) {
                    const aDate = parseTimestamp(aText);
                    const bDate = parseTimestamp(bText);
                    
                    if (aDate !== null && bDate !== null) {
                        return order === 'asc' ? aDate - bDate : bDate - aDate;
                    }
                }

                // Regular date formats detection
                const dateRegex = /^\d{4}-\d{2}-\d{2}(T|\s)?\d{2}:\d{2}(:\d{2})?$/;
                const simpleDate = /^\d{4}-\d{2}-\d{2}$/;
                
                if ((dateRegex.test(aText) || simpleDate.test(aText)) && 
                    (dateRegex.test(bText) || simpleDate.test(bText))) {
                    const aDate = new Date(aText);
                    const bDate = new Date(bText);
                    
                    // Only use date comparison if both are valid dates
                    if (!isNaN(aDate) && !isNaN(bDate)) {
                        const primaryComparison = order === 'asc' ? 
                            aDate.getTime() - bDate.getTime() : 
                            bDate.getTime() - aDate.getTime();
                            
                        if (primaryComparison !== 0 || lastSortColumn === null) {
                            return primaryComparison;
                        }
                    }
                }

                // If not dates, check if values are numbers for numeric sorting
                const aNumber = parseFloat(aText.replace(',', '.'));
                const bNumber = parseFloat(bText.replace(',', '.'));

                let primaryComparison;
                if (!isNaN(aNumber) && !isNaN(bNumber)) {
                    primaryComparison = order === 'asc' ? aNumber - bNumber : bNumber - aNumber;
                } else {
                    primaryComparison = order === 'asc' ? 
                        aText.localeCompare(bText, undefined, { numeric: true, sensitivity: 'base' }) : 
                        bText.localeCompare(aText, undefined, { numeric: true, sensitivity: 'base' });
                }

                if (primaryComparison !== 0 || lastSortColumn === null) {
                    return primaryComparison;
                }

                // Secondary sort by last sorted column
                const aLastCell = a.querySelector(`td[data-field='${lastSortColumn}']`);
                const bLastCell = b.querySelector(`td[data-field='${lastSortColumn}']`);

                let aLastText, bLastText;

                // Get displayed text from last sorted cells
                if (aLastCell) {
                    const aLastSelect = aLastCell.querySelector('select');
                    const aLastInput = aLastCell.querySelector('input');
                    if (aLastSelect) {
                        aLastText = aLastSelect.options[aLastSelect.selectedIndex] ? aLastSelect.options[aLastSelect.selectedIndex].text : '';
                    } else if (aLastInput) {
                        aLastText = aLastInput.value;
                    } else {
                        aLastText = aLastCell.innerText;
                    }
                } else {
                    aLastText = '';
                }

                if (bLastCell) {
                    const bLastSelect = bLastCell.querySelector('select');
                    const bLastInput = bLastCell.querySelector('input');
                    /*if (bLastSelect) {
                        bLastText = bLastSelect.options[bLastSelect.selectedIndex] ? bLastSelect.options[bLast.selectedIndex].text : '';
                    }*/
                    if (bLastSelect) {
                        bLastText = bLastSelect.options[bLastSelect.selectedIndex] ? bLastSelect.options[bLastSelect.selectedIndex].text : '';
                    }
                    else if (bLastInput) {
                        bLastText = bLastInput.value;
                    } else {
                        bLastText = bLastCell.innerText;
                    }
                } else {
                    bLastText = '';
                }

                // Trim the text values
                aLastText = aLastText.trim();
                bLastText = bLastText.trim();

                // Check if values are numbers for numeric sorting
                const aLastNumber = parseFloat(aLastText.replace(',', '.'));
                const bLastNumber = parseFloat(bLastText.replace(',', '.'));

                if (!isNaN(aLastNumber) && !isNaN(bLastNumber)) {
                    return lastSortOrder === 'asc' ? aLastNumber - bLastNumber : bLastNumber - aLastNumber;
                } else {
                    return lastSortOrder === 'asc' ?
                        aLastText.localeCompare(bLastText, undefined, { numeric: true, sensitivity: 'base' }) :
                        bLastText.localeCompare(aLastText, undefined, { numeric: true, sensitivity: 'base' });
                }
            });

            // Speichere Sortierung in Session
            fetch('save_sort.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    column: column,
                    order: order
                })
            });

            rows.forEach(row => table.appendChild(row));

            lastSortColumn = column;
            lastSortOrder = order;
        }

        // Function to check if a column likely contains timestamp data
        function checkIfTimestampColumn(column) {
            // Check column name for common timestamp-related terms
            const timestampKeywords = ['date', 'time', 'timestamp', 'created', 'modified', 'updated'];
            const columnLower = column.toLowerCase();
            
            for (const keyword of timestampKeywords) {
                if (columnLower.includes(keyword)) {
                    return true;
                }
            }
            
            // Check data types in column
            const cells = document.querySelectorAll(`td[data-field='${column}']`);
            let dateCount = 0;
            
            for (let i = 0; i < Math.min(cells.length, 5); i++) {
                const cell = cells[i];
                const input = cell.querySelector('input');
                
                if (input && (input.type === 'date' || input.type === 'datetime-local')) {
                    return true;
                }
                
                // Check if content matches common date patterns
                const text = cell.textContent.trim();
                if (isTimestampFormat(text)) {
                    dateCount++;
                }
            }
            
            // If more than half of the checked cells contain timestamp-like data
            return dateCount > 2;
        }

        // Function to parse different timestamp formats
        function parseTimestamp(value) {
            if (!value) return null;
            
            // Handle empty values
            value = value.trim();
            if (value === '' || value.toLowerCase() === 'null') return null;
            
            // Try different timestamp formats
            const formats = [
                // ISO format: 2023-01-30T15:30:45
                (val) => {
                    if (/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}(:\d{2})?$/.test(val)) {
                        const date = new Date(val);
                        return isNaN(date) ? null : date.getTime();
                    }
                    return null;
                },
                // MySQL format: 2023-01-30 15:30:45
                (val) => {
                    if (/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}(:\d{2})?$/.test(val)) {
                        const date = new Date(val.replace(' ', 'T'));
                        return isNaN(date) ? null : date.getTime();
                    }
                    return null;
                },
                // Simple date: 2023-01-30
                (val) => {
                    if (/^\d{4}-\d{2}-\d{2}$/.test(val)) {
                        const date = new Date(val);
                        return isNaN(date) ? null : date.getTime();
                    }
                    return null;
                },
                // European format: 30.01.2023
                (val) => {
                    if (/^\d{2}\.\d{2}\.\d{4}$/.test(val)) {
                        const parts = val.split('.');
                        const date = new Date(`${parts[2]}-${parts[1]}-${parts[0]}`);
                        return isNaN(date) ? null : date.getTime();
                    }
                    return null;
                },
                // US format: 01/30/2023
                (val) => {
                    if (/^\d{1,2}\/\d{1,2}\/\d{4}$/.test(val)) {
                        const date = new Date(val);
                        return isNaN(date) ? null : date.getTime();
                    }
                    return null;
                },
                // Timestamp as number
                (val) => {
                    const num = parseInt(val, 10);
                    if (!isNaN(num) && num > 946684800000) { // Jan 1, 2000
                        return num;
                    }
                    return null;
                }
            ];
            
            // Try each format until one works
            for (const format of formats) {
                const timestamp = format(value);
                if (timestamp !== null) {
                    return timestamp;
                }
            }
            
            return null;
        }

        // Function to check if a string looks like a timestamp
        function isTimestampFormat(text) {
            // Common date/time patterns
            return /\d{4}-\d{2}-\d{2}/.test(text) || // YYYY-MM-DD
                   /\d{2}\.\d{2}\.\d{4}/.test(text) || // DD.MM.YYYY
                   /\d{2}\/\d{2}\/\d{4}/.test(text) || // MM/DD/YYYY
                   /\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/.test(text) || // ISO datetime
                   /\d{4}-\d{2}-\d{2} \d{2}:\d{2}/.test(text); // SQL datetime
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
            // Show a loading indicator
            const insertButton = document.getElementById('insertDefaultButton');
            if (insertButton) {
                insertButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Lade...';
                insertButton.disabled = true;
            }

            // Create error handler function
            const handleError = (message) => {
                if (insertButton) {
                    insertButton.innerHTML = 'Einfügen';
                    insertButton.disabled = false;
                }
                alert(message);
            };

            const xhr = new XMLHttpRequest();
            xhr.open("POST", "ajax.php", true);
            xhr.setRequestHeader("Content-Type", "application/json");
            
            // Set timeout to avoid infinite waiting
            xhr.timeout = 30000; // 30 seconds
            
            xhr.ontimeout = function() {
                handleError("Die Anfrage hat zu lange gedauert und wurde abgebrochen.");
            };
            
            xhr.onerror = function() {
                handleError("Netzwerkfehler beim Laden der Tabellenstruktur.");
            };

            const data = JSON.stringify({
                action: 'get_table_structure',
                tabelle: tabelle,
                selectedTableID: '<?=$selectedTableID?>'
            });

            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4) {
                    // Reset button state
                    if (insertButton) {
                        insertButton.innerHTML = 'Einfügen';
                        insertButton.disabled = false;
                    }

                    if (xhr.status === 200) {
                        try {
                            // Try to safely parse the response
                            let responseText = xhr.responseText.trim();
                            if (!responseText) {
                                throw new Error("Empty response from server");
                            }
                            
                            const response = JSON.parse(responseText);
                            
                            if (response.status === "success") {
                                // Check if columns are available
                                if (response.columns && response.columns.length > 0) {
                                    populateInsertModal(response.columns, response.foreignKeys || {}, response.configQuery);
                                    $('#insertModal').modal('show');
                                } else {
                                    // If we have a config query but no columns, try to extract them on the client side
                                    const configQuery = response.configQuery;
                                    if (configQuery) {
                                        // Simple regex to extract column names from the SELECT part
                                        const selectMatch = configQuery.match(/SELECT\s+(.+?)\s+FROM/i);
                                        if (selectMatch) {
                                            const selectPart = selectMatch[1];
                                            const columnNames = selectPart.split(',')
                                                .map(item => {
                                                    // Try to extract column name or alias
                                                    const asMatch = item.match(/\s+[Aa][Ss]\s+[`'"]?([a-zA-Z0-9_]+)[`'"]?\s*$/);
                                                    if (asMatch) return asMatch[1];
                                                    
                                                    const dotMatch = item.match(/\.([`'"]?)([a-zA-Z0-9_]+)\1\s*$/);
                                                    if (dotMatch) return dotMatch[2];
                                                    
                                                    const simpleMatch = item.match(/([`'"]?)([a-zA-Z0-9_]+)\1\s*$/);
                                                    if (simpleMatch) return simpleMatch[2];
                                                    
                                                    return null;
                                                })
                                                .filter(name => name && name.toLowerCase() !== 'id')
                                                .map(name => ({ Field: name, Type: 'text' }));
                                            
                                            if (columnNames.length > 0) {
                                                populateInsertModal(columnNames, response.foreignKeys || {}, configQuery);
                                                $('#insertModal').modal('show');
                                                return;
                                            }
                                        }
                                    }
                                    
                                    // If we still didn't get columns, show an error
                                    handleError("Konnte keine Spalteninformationen aus der Abfrage extrahieren.");
                                }
                            } else {
                                handleError("Fehler beim Laden der Tabellenstruktur: " + (response.message || "Unbekannter Fehler"));
                            }
                        } catch (e) {
                            handleError("Fehler beim Verarbeiten der Serverantwort: " + e.message);
                        }
                    } else {
                        handleError("Serverfehler beim Laden der Tabellenstruktur. Status: " + xhr.status);
                    }
                }
            };

            try {
                xhr.send(data);
            } catch (e) {
                handleError("Fehler beim Senden der Anfrage: " + e.message);
            }
        }

        function populateInsertModal(columns, foreignKeys, configQuery) {
            const form = document.getElementById('insertForm');
            if (!form) {
                alert("Insert form not found");
                return;
            }
            form.innerHTML = '';

            // Hole die Reihenfolge und Namen aus den Table-Headers
            let headers = [];
            const tableHeaders = document.querySelectorAll('table thead th[data-field]');
            if (tableHeaders && tableHeaders.length > 0) {
                headers = Array.from(tableHeaders)
                    .map(th => th.getAttribute('data-field'))
                    .filter(field => field && field !== 'id');
            } else {
                headers = columns.map(col => col.Field || col.field || col.name || col.Name)
                    .filter(field => field && field !== 'id');
            }
            if (headers.length === 0) {
                alert("Fehler: Keine Feldnamen gefunden.");
                return;
            }

            headers.forEach(fieldName => {
                const div = document.createElement('div');
                div.className = 'form-group mb-3';

                // Info-Label-Handling
                let isInfo = false;
                let displayFieldName = fieldName; 
                if (fieldName.startsWith('info:')) {
                    isInfo = true;
                    displayFieldName = fieldName.substring(5);
                }

                const label = document.createElement('label');
                label.className = 'form-label';
                label.textContent = displayFieldName;
                div.appendChild(label);

                // FK-Select
                if (foreignKeys && foreignKeys[fieldName]) {
                    // Create select dropdown for foreign key fields
                    const select = document.createElement('select');
                    select.className = 'form-control';
                    select.name = fieldName;
                    
                    // Add NULL option
                    const nullOption = document.createElement('option');
                    nullOption.value = "NULL";
                    nullOption.textContent = "<?=NULL_WERT?>";
                    select.appendChild(nullOption);
                    
                    // Count valid options
                    const validOptions = [];
                    
                    // Add all foreign key options
                    if (foreignKeys[fieldName] && foreignKeys[fieldName].length > 0) {
                        foreignKeys[fieldName].forEach(fk => {
                            if (fk && fk.id !== undefined && fk.anzeige !== undefined) {
                                const option = document.createElement('option');
                                option.value = fk.id;
                                option.textContent = fk.anzeige;
                                select.appendChild(option);
                                validOptions.push(option);
                            }
                        });
                    }
                    
                    // If there's only one valid option (besides NULL), automatically select it
                    if (validOptions.length === 1) {
                        validOptions[0].selected = true;
                    }

                    div.appendChild(select);
                } else {
                    // Input für normale Felder und info:-Felder
                    const input = document.createElement('input');
                    input.className = 'form-control';
                    input.name = fieldName;
                    input.placeholder = "<?=NULL_WERT?>";

                    // Nur für Felder ohne info:-Prefix Typ bestimmen
                    if (!isInfo) {
                        // Finde passenden Spaltendefinitionseintrag für diesen Header
                        let column = columns.find(col => {
                            let colName = col.Field || col.field || col.name || col.Name;
                            return colName === fieldName;
                        });
                        if (column) {
                            const columnType = column.Type || column.type || 'text';
                            if (columnType.includes('date')) {
                                input.type = columnType.includes('datetime') ? 'datetime-local' : 'date';
                            } else if (columnType.includes('int') || columnType.includes('decimal') || columnType.includes('float') || columnType.includes('double')) {
                                input.type = 'number';
                                if (columnType.includes('decimal') || columnType.includes('float') || columnType.includes('double')) {
                                    input.step = '0.01';
                                }
                            } else {
                                input.type = 'text';
                            }
                        } else {
                            input.type = 'text';
                        }
                    } else {
                        // info:-Felder immer als readonly text
                        input.type = 'text';
                        input.readOnly = true;
                        input.style.backgroundColor = "#f5f5f5";
                    }

                    div.appendChild(input);
                }

                form.appendChild(div);
            });
        }

        function saveNewRecord() {
            const form = document.getElementById('insertForm');
            const formData = new FormData(form);
            const data = {};
            formData.forEach((value, key) => data[key] = value === "" ? null : value);

            const xhr = new XMLHttpRequest();
            xhr.open("POST", "ajax.php", true);
            xhr.setRequestHeader("Content-Type", "application/json");

            const requestData = JSON.stringify({
                action: 'insert_record',
                tabelle: '<?=$tabelle?>',
                data: data
            });

            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.status === "success") {
                            $('#insertModal').modal('hide');
                            resetPage();
                        } else {
                            alert("Fehler beim Speichern" + (response.message ? ": " + response.message : ""));
                        }
                    } catch (e) {
                        alert("Fehler beim Verarbeiten der Serverantwort.");
                    }
                }
            };

            xhr.send(requestData);
        }

        function toggleSelectAll(source) {
            const checkboxes = document.querySelectorAll('.row-checkbox');
            checkboxes.forEach(checkbox => {
                // Only select checkboxes in visible rows
                const row = checkbox.closest('tr');
                if (row && row.style.display !== 'none') {
                    checkbox.checked = source.checked;
                }
            });
        }

        function toggleRowSelection(checkbox) {
            // Funktionalität kann hier erweitert werden, falls nötig
        }

        function deleteSelectedRows(tabelle) {
            const deleteButton = document.getElementById('deleteSelectedButton');
            const originalText = deleteButton.innerHTML;

            const selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.getAttribute('data-id'));
            if (selectedIds.length === 0) {
                alert('Keine Zeilen ausgewählt.');
                return;
            }

            // Collect data to display in confirmation dialog
            const selectedData = selectedIds.map(id => {
                const row = document.querySelector(`tr[data-id='${id}']`);
                if (row) {
                    const cells = Array.from(row.querySelectorAll('td')).slice(1).map(td => { // Exclude the first column (checkbox)
                        const input = td.querySelector('input');
                        const select = td.querySelector('select');
                        if (input) {
                            return input.value.trim();
                        } else if (select) {
                            return select.options[select.selectedIndex].text.trim();
                        } else {
                            return td.innerText.trim();
                        }
                    });
                    return cells.join(' | ');
                }
                return '';
            });

            const displayData = selectedData.slice(0, 5).join('\n');
            const additionalCount = selectedData.length > 5 ? `\n...und ${selectedData.length - 5} weitere` : '';
            const confirmationMessage = `Wollen Sie diese ${selectedData.length} Datensätze löschen?\n\n${displayData}${additionalCount}`;

            const confirmation = confirm(confirmationMessage);
            if (!confirmation) return;

            // Show spinner and disable button
            deleteButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Lösche...';
            deleteButton.disabled = true;

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
                            resetPage(); // Reload the page to see the changes
                        } else {
                            alert("Fehler beim Löschen der Daten.");
                        }
                    } catch (e) {
                        alert("Fehler beim Verarbeiten der Serverantwort.");
                    }
                } else if (xhr.readyState === 4 && xhr.status !== 200) {
                    alert("Serverfehler beim Löschen der Daten.");
                }

                // Setze den Button zurück und aktiviere ihn wieder
                // Automatisch nach reload.
                // deleteButton.innerHTML = originalText;
                // deleteButton.disabled = false;
            };

            xhr.send(data);
        }

        function resetPage(){
            const resetButton = document.getElementById('resetButton');
            const originalText = resetButton.innerHTML;

            // Show spinner and disable button
            resetButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Lade...';
            resetButton.disabled = true;

            caches.keys().then(function(names) {
                for (let name of names) caches.delete(name);
            }).then(function() {
                location.reload(true);
            });
        }

        // Neue Funktion für Container-Management 
        // Scrollbars werden entdeckt, aber die Klasse wird nicht auf fluid geändert.
        function adjustContainer() {
            const table = document.querySelector('.table');
            const container = document.querySelector('.table-container');
            const containerParent = container.parentElement;
            
            if (!table || !container || !containerParent) return;
            
            // Start with container
            containerParent.classList.remove('container-fluid');
            containerParent.classList.add('container');
            container.style.overflowX = 'auto';
            
            // Force layout recalculation
            void containerParent.offsetWidth;
            
            // Check if there's a horizontal scrollbar
            const hasScrollbar = container.scrollWidth > container.clientWidth;
            
            // Switch to container-fluid if there's a scrollbar
            if (hasScrollbar) {//alert("hasScrollbar");
                $('.flex-container ').removeClass('container');
                $('.flex-container ').addClass('container-fluid');
                //containerParent.classList.remove('container');
                //containerParent.classList.add('container-fluid');
            } else {
                containerParent.classList.remove('container-fluid');
                containerParent.classList.add('container');
            }
        }

        function setButtonHeights() {
            const referenceButton = document.getElementById('check-duplicates');
            if (referenceButton) {
                const height = referenceButton.offsetHeight + 'px';
                const buttons = document.querySelectorAll('.btn-group-container .btn, .btn-group-container .btn-group .btn');
                buttons.forEach(button => {
                    button.style.height = height;
                });
                const exportButton = document.querySelector('.btn-group .btn.dropdown-toggle');
                if (exportButton) {
                    exportButton.style.height = (referenceButton.offsetHeight - 1) + 'px';
                }
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Warte kurz bis Layout stabil ist
            setTimeout(adjustContainer, 100);
            
            // Bereits existierender Code
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
            const checkDuplicatesButton = document.getElementById('check-duplicates');
            if (checkDuplicatesButton) {
                checkDuplicatesButton.addEventListener('click', checkDubletten);
            }

            // Container beim Laden anpassen
            // adjustContainer();
            
            // Container bei Größenänderung anpassen
            window.addEventListener('resize', adjustContainer);

            // Set button heights
            setButtonHeights();
        });

        function exportData(format, spreadsheetFormat) {
            const filterInput = document.getElementById('tableFilter');
            const isFiltered = filterInput && filterInput.value.trim() !== '';
            let exportAll = true;

            if (isFiltered) {
                exportAll = !confirm('Die Daten sind gefiltert. Möchten Sie nur die gefilterten Daten exportieren?\n\nOK = Nur gefilterte Daten\nAbbrechen = Alle Daten');
            }

            const visibleRows = [];
            if (!exportAll) {
                document.querySelectorAll('table tbody tr').forEach(row => {
                    if (row.style.display !== 'none') {
                        visibleRows.push(row.getAttribute('data-id'));
                    }
                });
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'export.php';
            form.target = '_blank';

            const params = {
                format: format,
                tabelle: '<?=$tabelle?>',
                tabid: '<?=$selectedTableID?>',
                exportAll: exportAll ? '1' : '0',
                ids: visibleRows.join(','),
                spreadsheet_format: spreadsheetFormat
            };

            for (const key in params) {
                if (params[key]) {  // Nur nicht-leere Werte hinzufügen
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = params[key];
                    form.appendChild(input);
                }
            }

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }
    
        function filter_that(selectElement, typ){
            
            switch(typ){
                case 'select':
                    var selectedText = selectElement.options[selectElement.selectedIndex].text;
                    break;
                case 'input':
                    var selectedText = selectElement.value;
                    break;
                case 'div':
                    var selectedText = selectElement.innerHTML;
                    break;
            }

            if(document.getElementById('tableFilter').value==selectedText){
                selectedText = "";
            }

            event.preventDefault();
            document.getElementById('tableFilter').value = selectedText;
            filterTable();
            
        }

        function clearFilter() {
            const filterInput = document.getElementById('tableFilter');
            if (filterInput) {
                filterInput.value = '';
                filterTable();
            }
        }

    </script>

</head>
<body>
 
<?php
// Manche Spalten sind per ID via Fremdschlüssel zu anderen Tabellen verknüpft. Die ID anzuzeigen (und zu bearbeiten) 
// bringt dem Anwender wenig. Es muss daher in config pro FK eine Referenzquery definiert werden, die die ID in eine für den
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
    
    echo '<p><form method="get" class="d-flex align-items-center">';
    echo '<select id="tableSelectBox" name="tab" class="form-control me-2" onchange="this.form.submit()">';

    if(!isset($anzuzeigendeDaten[$selectedTableID])){
        echo '<option value="">-- Tabelle wählen --</option>';
    }
    /*$maxLaenge = 0;
    $trennerindizies = array();
    $options = array();
    foreach ($anzuzeigendeDaten as $index => $table) {
        if(isset($table['trenner'])){
            $trennerindizies[] = count($options);
            $options[] = $table['trenner'];
        }else{
            $tableName = htmlspecialchars($table['tabellenname']);
            $tableComment = htmlspecialchars($table['auswahltext']);
            if(strlen($tableComment) > $maxLaenge) $maxLaenge = strlen($tableComment);
            $displayText = !empty($tableComment) ? "$tableComment" : $tableName;
            $selected = ($index == $selectedTableID) ? 'selected' : '';
            $options[] = '<option value="' . $index . '" ' . $selected . '>' . $displayText . '</option>';
        }
    }

    foreach ($trennerindizies als $trennerindex) {
        $firstChar = substr($options[$trennerindex], 0, 1);
        $displayText = str_pad('', $maxLaenge, $firstChar);
        $options[$trennerindex] = '<option disabled>' . $displayText . '</option>';
    }*/

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
            $options[] = '<option value="' . $index . '" ' . $selected . '>' . $displayText . '</option>';
        }
    }
    
    // Vereinfachte Trennstriche - immer 5 Striche
    foreach ($trennerindizies as $trennerindex) {
        $options[$trennerindex] = '<option disabled>-----</option>';
    }

    echo implode("\n", $options);
    echo '</select>';
    
    // Add Impressum button right next to the dropdown in the same form
    echo '<a href="./user_code/impressum.php" target="_blank" class="btn btn-secondary ms-2" style="white-space: nowrap;">Impressum und Datenschutzerklärung</a>';
    echo '<a href="doc/hilfe.html" target="_blank" class="btn btn-secondary ms-2">?</a>';
    
    echo '</form></p>';
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
    global $readwrite;
    global $deleteAnyway;
    
    if (!empty($data)) {
        if($readwrite || $deleteAnyway)
            echo "<th style='width: 60px'><div class='checkbox-header-container p-2'><input type='checkbox' class='form-check-input' onclick='toggleSelectAll(this)'></div></th>"; // Checkbox for selecting all rows
        foreach (array_keys($data[0]) as $header) {
            $style = "";
            if(isset($anzuzeigendeDaten[$selectedTableID]['spaltenbreiten'][$header])) {
                $style = "style='width: ".$anzuzeigendeDaten[$selectedTableID]['spaltenbreiten'][$header]."px;'";
            }
            if (strcasecmp($header, 'id') !== 0) {
                // Check if it's an info column and extract the real display name
                $displayHeader = $header;
                if (strpos($header, 'info:') === 0) {
                    $displayHeader = substr($header, 5); // Remove 'info:' prefix
                }
                echo "<th $style data-field='" . htmlspecialchars($header) . "'>" . htmlspecialchars($displayHeader) . "</th>";
            }
        }
    } else {
        if ($selectedTableID !== "") {
            echo "<div class='container mt-4'><div class='alert alert-light' role='alert'>Diese Liste ist noch leer.</div></div>";
        } else {
            //echo "<div class='container mt-4'><div class='alert alert-light' role='alert'>Bitte wählen Sie eine Tabelle aus.</div></div>";
            echo "<style>::-webkit-scrollbar { display: none; }</style><div class='container mt-4' style='overflow-y: hidden;'><div class='alert alert-light' role='alert'>Bitte wählen Sie eine Tabelle aus.</div></div>";
        }
    }
}

function renderTableRows($data, $readwrite, $deleteAnyway, $tabelle, $foreignKeys) {
    global $db;
    global $anzuzeigendeDaten;
    global $selectedTableID;

    // Eingabemethode (z.B. Date-Picker) nach Datentyp wählen.
    $columns = $db->query("SHOW COLUMNS FROM $tabelle"); 
    $columnTypes = [];
    foreach ($columns['data'] as $column) {
        $columnTypes[$column['Field']] = $column['Type'];
    }

    foreach ($data as $row) {
        echo '<tr data-id="' . $row['id'] . '">';
        if($readwrite || $deleteAnyway)
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
                // Add word-wrap styles
                $style .= "word-wrap: break-word; white-space: normal;'";
                
                echo '<td data-field="' . htmlspecialchars($key) . '" ' . $style . '>';
                $data_fk_ID_key = "";
                $data_fk_ID_value = "";
                
                // Check if this is an info column (starts with 'info:')
                $isInfoColumn = strpos($key, 'info:') === 0;
               
                if(isset($foreignKeys[$key])) { 
                    foreach($foreignKeys[$key] as $fk){
                        if($fk['id'] == $value){
                            $data_fk_ID_key = $fk['id'];
                            $data_fk_ID_value = $fk['anzeige'];
                            break;
                        }
                    }

                    if ($readwrite || $deleteAnyway) {
                        echo '<select oncontextmenu="filter_that(this, \'select\');" class="form-control border-0" style="background-color: inherit; word-wrap: break-word; white-space: normal;" onchange="updateField(\'' . $tabelle . '\', \'' . $row['id'] . '\', \'' . $key . '\', this.value, 0)">';
                        echo '<option value="NULL"' . (empty($value) ? ' selected' : '') . '>'.NULL_WERT.'</option>';
                        foreach ($foreignKeys[$key] as $fk) {
                            $fk_value = $fk['id'];
                            $fk_display = htmlspecialchars($fk['anzeige'], ENT_QUOTES);

                            $selected = ($fk_value == $value) ? 'selected' : '';
                            echo '<option value="' . htmlspecialchars($fk_value, ENT_QUOTES) . '" ' . $selected . '>' . $fk_display . '</option>';
                        }
                        echo '</select>';
                    } else {
                        echo '<div oncontextmenu="filter_that(this, \'div\');" style="word-wrap: break-word; white-space: normal;">' . htmlspecialchars($data_fk_ID_value, ENT_QUOTES) . '</div>';
                    }
                } else {
                    if ($readwrite && !$isInfoColumn) {
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
                        echo '<input oncontextmenu="filter_that(this, \'input\');" data-type="'.htmlspecialchars($columnType).'" data-fkIDkey="' . htmlspecialchars($data_fk_ID_key, ENT_QUOTES) . '" 
                              data-fkIDvalue="' . htmlspecialchars($data_fk_ID_value, ENT_QUOTES) . '" 
                              type="' . $inputType . '" 
                              class="form-control border-0" 
                              style="background-color: inherit; word-wrap: break-word; white-space: normal;" 
                              value="' . htmlspecialchars($value, ENT_QUOTES) . '"
                              onchange="updateField(\'' . $tabelle . '\', \'' . $row['id'] . '\', \'' . $key . '\', this.value, \'' . htmlspecialchars($columnType, ENT_QUOTES) . '\')"
                              onfocus="clearCellColor(this)">';
                
                    } else {
                        if(isset($columnType)){
                            if (strpos($columnType, 'decimal') !== false || strpos($columnType, 'float') !== false) {
                                $value = number_format((float)$value, 2, '.', '');
                            }
                        }
                        echo '<div oncontextmenu="filter_that(this, \'div\');" style="word-wrap: break-word; white-space: normal;">' . htmlspecialchars($value, ENT_QUOTES) . '</div>';
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
            }?>
            
            <?php 
            if($tabelle!=""){
                echo "<p><form class='d-flex align-items-center'><input type='text' id='tableFilter' class='form-control' placeholder='Filtern entweder durch manuelle Eingabe oder Rechtsklick auf ein Datenfeld.'>";
                // Löschen-Button auskommentiert
                //echo "<button id='clearFilterButton' type='button' class='btn btn-secondary ms-2' onclick='clearFilter()'>Löschen</button>";
                echo "</form></p>";
            }
            ?>
            <?php 
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
                                <!--li><a class="dropdown-item" href="#" onclick="exportData('csv')">Als CSV</a></li-->
                                <li><a class="dropdown-item" href="#" onclick="exportData('excel', 'Xlsx')">Als Excel</a></li>
                                <!--li><a class="dropdown-item" href="#" onclick="exportData('excel', 'Ods')">Als LibreOffice</a></li-->
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
                    if (!empty($data)) renderTableRows($data, $readwrite, $deleteAnyway, $tabelle, $FKdata);
                ?>
                </tbody>
            </table>
        </div>  

    </div>

    <!-- Insert Modal -->
    <div class="modal fade" id="insertModal" tabindex="-1" aria-labelledby="insertModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="insertModalLabel">Neuen Datensatz erstellen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="insertForm">
                        <!-- Felder werden dynamisch eingefügt -->
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    <button type="button" class="btn btn-primary" onclick="saveNewRecord()">Speichern</button>
                </div>
            </div>
        </div>
    </div>

    </body>
    
    <script>
// Replace the old tooltip code with Bootstrap tooltips
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => 
        new bootstrap.Tooltip(tooltipTriggerEl, {
            container: 'body',
            boundary: 'window',
            animation: true,
            html: false
        })
    );

    // Add tooltips dynamically to table cells
    document.addEventListener('mouseover', function(e) {
        const td = e.target.closest('td');
        if (td && !td.hasAttribute('data-bs-toggle')) {
            // Skip checkbox cells
            const hasCheckbox = td.querySelector('.form-check-input') || 
                               td.querySelector('.checkbox-container');
            if (hasCheckbox) return;
            
            let text = "";
            const input = td.querySelector('input');
            if (input) {
                text = input.value;
            } else {
                const select = td.querySelector('select');
                if (select && select.selectedIndex >= 0) {
                    text = select.options[select.selectedIndex].text;
                } else {
                    text = td.innerText.trim();
                }
            }
            
            // Only show tooltip if text is not empty
            if (text) {
                td.setAttribute('data-bs-toggle', 'tooltip');
                td.setAttribute('data-bs-title', text);
                td.setAttribute('data-bs-placement', 'auto');
                td.setAttribute('data-bs-container', 'body');
                
                // Create the tooltip
                new bootstrap.Tooltip(td, {
                    container: 'body',
                    boundary: 'window'
                });
                
                // Show it immediately if mouse is already over
                const tooltip = bootstrap.Tooltip.getInstance(td);
                if (tooltip) {
                    tooltip.show();
                }
            }
        }
    });

    // Clean up tooltips when no longer needed
    document.addEventListener('mouseout', function(e) {
        const td = e.target.closest('td');
        if (td) {
            const tooltip = bootstrap.Tooltip.getInstance(td);
            if (tooltip && typeof tooltip.hide === 'function') {
                tooltip.hide();
            }
        }
    });
});
    </script>
    <script language="javascript" type="text/javascript" src="./user_includes/index_document_ready.js"></script>

</html>