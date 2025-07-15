const errDB = 1;
const errSRV = 2;


function showErrorMsg(message) {
    const errorDiv = document.getElementById('insertErrorMsg');
    if (errorDiv) {
        errorDiv.innerHTML = message || "Unbekannter Fehler";
        errorDiv.classList.remove('d-none');
    } else {
        // Fallback: HTML-Tags entfernen für alert
        alert((message || "Unbekannter Fehler").replace(/<[^>]+>/g, ''));
    }
}

function updateField(tabelle, id, field, value, datatype, ajaxFile, element) { 
    
    let isUserAjax = 0;
    if (element && element.hasAttribute('data-userajax')) {
        isUserAjax = element.getAttribute('data-userajax');
    }     
        // selectedTableID als Zahl, falls vorhanden, sonst 0
        let tab = php_tab;
    
    if (isUserAjax>0) {
        ajaxFile = "./user_code/" + ajaxFile;
    }else{
        ajaxFile = "ajax.php";
    }

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
    xhr.open("POST", ajaxFile, true);
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
                    showErrorMsg("Fehler beim Update. Stimmt das Datenformat? Für Details siehe sys_log-Tabelle in der Datenbank.");
                }
            } catch (e) {
                markCellError(id, field);
                showErrorMsg("Fehler beim Verarbeiten der Serverantwort.");
            }
        } else if (xhr.readyState === 4 && xhr.status !== 200) {
            markCellError(id, field);
            showErrorMsg("Serverfehler beim Update. Stimmt das Datenformat? Für Details siehe sys_log-Tabelle in der Datenbank.");
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
                showErrorMsg("Fehler beim Abgleich der Zeile.");
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
  
        body: JSON.stringify({ action: 'check_duplicates', tabelle: php_tabelle })
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
            if (!column) return; // Fix: nur sortieren, wenn data-field existiert
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
        selectedTableID: php_selectedTableID
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
/*
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

    // Prüfe, ob alle Felder mit 'info:' beginnen
    // let $allFieldsAreInfo = true;
    // for (let i = 0; i < headers.length; i++) {
    //     if (!headers[i].startsWith('info:')) {
    //         $allFieldsAreInfo = false;
    //         break;
    //     }
    // }
    headers.forEach(
        fieldName => 
            {  // Info-Felder überspringen, wenn nicht ALLE Felder info: sind                    
                    // if (!$allFieldsAreInfo && fieldName.startsWith('info:')) return;
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

        // Ist die COL eine Spalte mit Referenzquery/"foreignKeys-Spalte", dann mach einen Select, sonst ein Input
        if (foreignKeys[fieldName]) {
            // Create select dropdown for foreign key fields
            const select = document.createElement('select');
            select.className = 'form-control';
            select.name = fieldName;
            
            // Add "---" Feld
           
            // const nullOption = document.createElement('option');
            // nullOption.value = "NULL";
            // nullOption.textContent = "< ?=NULL_WERT?>";
            // select.appendChild(nullOption);
            


            // Optionen sammeln
            const validOptions = []; 
            // Add all foreign key options
            if (foreignKeys[fieldName].length > 0) {
                foreignKeys[fieldName].forEach(fk => {
                    if (fk && fk.id !== undefined && fk.anzeige !== undefined) {                                
                        const option = document.createElement('option');
                        option.value = fk.id;
                        option.textContent = fk.anzeige;
                        select.appendChild(option);
                        validOptions.push(option);
                        if(fk.id==-1){
                            // Select nach dem Hinzufügen der Option deaktivieren
                            select.disabled = true;
                        }
                    }
                });
            }
            
            // If there's only one valid option (besides NULL), automatically select it
            if (validOptions.length === 1) {
                validOptions[0].selected = true;
            }else if(validOptions.length > 1){
                const pleaseChooseOption = document.createElement('option');
                pleaseChooseOption.value = "null";
                pleaseChooseOption.textContent = php_PLEASE_CHOOSE;
                pleaseChooseOption.disabled = true;
                pleaseChooseOption.selected = true;
                select.insertBefore(pleaseChooseOption, select.firstChild);
            }               
            
            div.appendChild(select); 
        } else { // else = no Foreign Key
            // Input für normale Felder und info:-Felder
            const input = document.createElement('input');
            input.className = 'form-control';
            input.name = fieldName;
            input.placeholder = ""; 

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
                        // Setze Standardwert auf heute, falls kein Default vorhanden
                        if (!input.value) {
                            const now = new Date();
                            if (input.type === 'date') {
                                input.value = now.toISOString().slice(0, 10);
                            } else if (input.type === 'datetime-local') {
                                input.value = now.toISOString().slice(0, 16);
                            }
                        }
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
}*/

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

    // Prüfe, ob alle Felder mit 'info:' beginnen
    // Hintergrund: Info-Felder werden im Modal als read/only angezeigt.
    // ABER: Wenn ALLE Felder Info sind, ist das ein Sonderfall
    // Das ist dann eine Liste, die zwar delete und insert hat, aber die Daten sollen
    // dort nicht editiert werden. Also aus irgendwelchen Gründen nur GANZE DATENSÄTZE
    // gehandelt werden. Also muss das Modal Eingaben zulassen.
    let allFieldsAreInfo = true;
    for (let i = 0; i < headers.length; i++) {
        if (!headers[i].startsWith('info:')) {
            allFieldsAreInfo = false;
            break;
        }
    }

    headers.forEach(
        fieldName => 
            {  
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

        // Verzweigung:
        // if FK-Spalte             => select
        // else                     => Text
        //                                      if noInfo => normal
        //                                      else      => readonly


        // Ist die COL eine Spalte mit Referenzquery/"foreignKeys-Spalte", dann mach einen Select, sonst ein Input
        let content = "";

        if (foreignKeys[fieldName]) {
            // Create select dropdown for foreign key fields
            const select = document.createElement('select');
            select.className = 'form-control';
            select.name = fieldName;
            
            // Optionen sammeln
            const validOptions = []; 
            // Add all foreign key options
            if (foreignKeys[fieldName].length > 0) {
                foreignKeys[fieldName].forEach(fk => {
                    if (fk && fk.id !== undefined && fk.anzeige !== undefined) {                                
                        const option = document.createElement('option');
                        option.value = fk.id;
                        option.textContent = fk.anzeige;
                        select.appendChild(option);
                        validOptions.push(option);
                        if(fk.id==-1){
                            // Select nach dem Hinzufügen der Option deaktivieren
                            select.disabled = true;
                        }
                    }
                });
            }
            
            // If there's only one valid option (besides NULL), automatically select it
            if (validOptions.length === 1) {
                validOptions[0].selected = true;
            }else if(validOptions.length > 1){
                const pleaseChooseOption = document.createElement('option');
                pleaseChooseOption.value = "null";
                pleaseChooseOption.textContent = php_PLEASE_CHOOSE;
                pleaseChooseOption.disabled = true;
                pleaseChooseOption.selected = true;
                select.insertBefore(pleaseChooseOption, select.firstChild);
            }               

        // INFO-Felder lassen sich als FK nicht als read-only-select anzeigen
        // read-only gibt es nicht und disabled submittet dann nicht die Daten.
        // Workaround: so tun als ob:
            if (isInfo && !allFieldsAreInfo) {
                select.style.backgroundColor = "#f5f5f5";
                select.style.pointerEvents = "none";
                select.style.opacity = "1";
            }
            
            div.appendChild(select); 
        } else { // else = no Foreign Key
            // Input für normale Felder und info:-Felder
            const input = document.createElement('input');
            input.className = 'form-control';
            input.name = fieldName;
            input.placeholder = ""; 

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
                        // Setze Standardwert auf heute, falls kein Default vorhanden
                        if (!input.value) {
                            const now = new Date();
                            if (input.type === 'date') {
                                input.value = now.toISOString().slice(0, 10);
                            } else if (input.type === 'datetime-local') {
                                input.value = now.toISOString().slice(0, 16);
                            }
                        }
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

    // Fehlerbereich zurücksetzen
    const errorDiv = document.getElementById('insertErrorMsg');
    if (errorDiv) {
        errorDiv.innerHTML = '';
        errorDiv.classList.add('d-none');
    }

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "ajax.php", true);
    xhr.setRequestHeader("Content-Type", "application/json");

    const requestData = JSON.stringify({
        action: 'insert_record',
        tabelle: php_tabelle,
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
                    errorManagement(response.message, errDB, response.error_code, response.error_ID)
                    
                }
            } catch (e) { 

                const response = JSON.parse(xhr.responseText); 
                
                errorManagement(response.message, errSRV, response.error_code, response.error_ID)
            }
        }
    };
    xhr.send(requestData);
}

function errorManagement(errorMessage, errSrc, sqlErrCode, error_log_ID){

  
    let src = "";
    if (errSrc==errDB) src = "database";
    else if (errSrc==errSRV) src = "ajax-call";
    
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "ajax_errormanagement.php", true);
    xhr.setRequestHeader("Content-Type", "application/json");
    xhr.send(JSON.stringify({
        action: 'error_occured',
        src: src,
        errorcode: sqlErrCode,
        errorMessage: errorMessage,
        error_log_ID: error_log_ID
    }));
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            // Done successfully
            // Optional: handle response if needed
            const response = JSON.parse(xhr.responseText); 
            if(response.status == "error"){
                if (isNaN(error_log_ID)) {
                    errorMessage = php_DB_ERROR.replace(/#FEHLERID#/g, "000");                                
                } else {
                    errorMessage = php_DB_ERROR.replace(/#FEHLERID#/g, error_log_ID);
                }
            }else{
                errorMessage = response.message;
            }
        }
        showErrorMsg(errorMessage);
    };


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
    const selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.getAttribute('data-id'));
    if (selectedIds.length === 0) {
        showErrorMsg('Keine Zeilen ausgewählt.');
        return;
    }

    // Collect data to display in confirmation dialog
    const selectedData = selectedIds.map(id => {
        const row = document.querySelector(`tr[data-id='${id}']`);
        if (row) {
            const cells = Array.from(row.querySelectorAll('td')).slice(1).map(td => {
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
            return cells;
        }
        return [];
    });

    // Tabellenkopf aus erster Zeile extrahieren
    let tableHead = '';
    const firstRow = document.querySelector('tr[data-id]');
    if (firstRow) {
        const ths = Array.from(firstRow.querySelectorAll('td')).slice(1).map(td => {
            let h = td.getAttribute('data-field') || '';
            h = h.replace(/^info:/, '').replace(/^ajax:/, '');
            return `<th class='delete-modal-th'>${h}</th>`;
        });
        tableHead = ths.join('');
    }

    let tableRows = '';
    selectedData.forEach((row, idx) => {
        tableRows += `<tr class='delete-modal-zebra-${idx % 2}'>${row.map(cell => `<td class='delete-modal-td'>${cell}</td>`).join('')}</tr>`;
    });

    const additionalCount = selectedData.length > 5 ? `<div class='text-muted mt-2'>...und ${selectedData.length - 5} weitere</div>` : '';
    const confirmationMessage = `Wollen Sie diese ${selectedData.length} Datensätze löschen?` +
        `<div class='table-responsive mt-3'><table class='table table-sm table-bordered' style='min-width:400px'>` +
        (tableHead ? `<thead style='background:#eee'><tr>${tableHead}</tr></thead>` : '') +
        `<tbody>${tableRows}</tbody></table></div>` + additionalCount + "<br>";

    // Modal für Delete vorbereiten
    document.getElementById('insertForm').style.display = 'none';
    document.getElementById('insertDeleteBody').innerHTML = confirmationMessage;
    document.getElementById('insertDeleteBody').style.display = '';
    document.getElementById('insertErrorMsg').classList.add('d-none');
    document.getElementById('insertSaveButton').classList.add('d-none');
    document.getElementById('insertDeleteButton').classList.remove('d-none');
    document.getElementById('insertCancelButton').innerText = 'Abbrechen';
    document.getElementById('insertModalLabel').innerText = 'Datensätze löschen';

    // Show modal
    const insertModal = new bootstrap.Modal(document.getElementById('insertModal'));
    insertModal.show();

    // Remove previous event listener to avoid stacking
    const confirmDeleteButton = document.getElementById('insertDeleteButton');
    confirmDeleteButton.onclick = function() {
        confirmDeleteButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Lösche...';
        confirmDeleteButton.disabled = true;

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "ajax.php", true);
        xhr.setRequestHeader("Content-Type", "application/json");

        const data = JSON.stringify({
            action: 'delete',
            tabelle: tabelle,
            ids: selectedIds
        });

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                confirmDeleteButton.innerHTML = 'Löschen';
                confirmDeleteButton.disabled = false;
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.status === "success") {
                            // Modal schließen und Seite neu laden
                            insertModal.hide();
                            resetPage();
                        } else {
                            // Fehlerhandling wie beim Insert: Fehler an errorManagement übergeben
                            errorManagement(response.message || "Fehler beim Löschen der Daten.", errDB, response.error_code, response.error_ID);
                        }
                    } catch (e) {
                        showErrorMsg("Fehler beim Verarbeiten der Serverantwort.");
                    }
                } else {
                    showErrorMsg("Serverfehler beim Löschen der Daten.");
                }
            }
        };
        xhr.send(data);
    };
}

// Zeigt das Insert-Modal wieder im Insert-Modus an
function showInsertModal() {
    document.getElementById('insertForm').style.display = '';
    document.getElementById('insertDeleteBody').innerHTML = '';
    document.getElementById('insertDeleteBody').style.display = 'none';
    document.getElementById('insertErrorMsg').classList.add('d-none');
    document.getElementById('insertSaveButton').classList.remove('d-none');
    document.getElementById('insertDeleteButton').classList.add('d-none');
    document.getElementById('insertCancelButton').innerText = 'Abbrechen';
    document.getElementById('insertModalLabel').innerText = 'Neuen Datensatz erstellen';
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

    // #####################################################################

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

    // #####################################################################
    // Sortieren und Filtern

    // Datentypen erkennen und anzeigen
    setTimeout(detectColumnDataTypes, 100); // Kurze Verzögerung, um sicherzustellen, dass alle Daten geladen sind


    addSortEventListeners();
    const filterInput = document.getElementById('tableFilter');
    if (filterInput) {
        filterInput.addEventListener('input', filterTable);
        filterInput.value = ''; // Clear filter field on page load
    }
    const insertButton = document.getElementById('insertDefaultButton');
    if (insertButton) {
        insertButton.addEventListener('click', function() {
            insertDefaultRecord(php_tabelle);
        });
    }
    const deleteButton = document.getElementById('deleteSelectedButton');
    if (deleteButton) {
        deleteButton.addEventListener('click', function() {
            deleteSelectedRows(php_tabelle);
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

    // Spaltenfilter-Logik aktivieren
    const columnFilters = document.querySelectorAll('.column-filter');
    columnFilters.forEach(input => {
        input.value = ''; // Clear column filter fields on page load
        input.addEventListener('input', filterTableByColumns);
        input.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            this.value = '';
            filterTableByColumns();
        });
    });

    // Überwache Änderungen in den Spaltenfiltern
    columnFilters.forEach((filter, index) => {
        filter.addEventListener('input', () => {
            updateURLWithFilters(columnFilters);
        });
    });

    // Überwache Änderungen im globalen Filter
    const globalFilter = document.getElementById('tableFilter');
    if (globalFilter) {
        globalFilter.addEventListener('input', () => {
            updateURLWithFilters(document.querySelectorAll('.column-filter'), globalFilter);
        });
    }
    
    const columnFilterInputs = document.querySelectorAll('.column-filter');
    columnFilterInputs.forEach((input, index) => {
        const filterValue = php_spaltenfilter[index + 1] || "";
        input.value = filterValue;
        input.addEventListener('input', filterTableByColumns);
        input.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            this.value = '';
            filterTableByColumns();
        });
    });
    if(filteredByGET) filterTableByColumns();   
});

function updateURLWithFilters(filters, globalFilter = null) {
    const url = new URL(window.location.href);

    // Entferne nur die Filter-Parameter, die mit s1, s2, ... beginnen
    Array.from(url.searchParams.keys()).forEach(key => {
        if (key.startsWith('s') && key !== 's0') {
            url.searchParams.delete(key);
        }
    });

    // Füge globalen Filter hinzu, falls vorhanden
    if (globalFilter && globalFilter.value.trim() !== '') {
        url.searchParams.set('s0', globalFilter.value.trim());
    }

    // Füge Spaltenfilter hinzu
    filters.forEach((filter, index) => {
        if (filter.value.trim() !== '') {
            url.searchParams.set(`s${index + 1}`, filter.value.trim());
        }
    });

    // Aktualisiere die Adresszeile, ohne die Seite neu zu laden
    window.history.replaceState({}, '', url);
}

function exportData(format, spreadsheetFormat) {
    const filterInput = document.getElementById('tableFilter');
    const isFiltered = filterInput && filterInput.value.trim() !== '';
    let exportAll = true;

    if (isFiltered) {
        exportAll = !confirm('Die Daten sind gefiltert. Möchten Sie nur die gefilterten Daten exportieren?\n\nOK = Nur gefilterte Daten\nAbbrechen = Alle Daten');
    }

    const visibleRows = [];
    const visibleData = [];
    if (!exportAll) {
        // Collect visible row IDs and all cell data
        const table = document.querySelector('table');
        const headers = Array.from(table.querySelectorAll('thead th[data-field]')).map(th => th.getAttribute('data-field'));
        table.querySelectorAll('tbody tr').forEach(row => {
            if (row.style.display !== 'none') {
                visibleRows.push(row.getAttribute('data-id'));
                // Collect cell data for this row
                const rowData = {};
                rowData['id'] = row.getAttribute('data-id');
                let cellIdx = 0; 
                row.querySelectorAll('td[data-field]').forEach(cell => { 
                    const key = headers[cellIdx];
                    const input = cell.querySelector('input');
                    if (input) {
                        rowData[key] =  input.value.trim();
                    } else {
                        rowData[key] =   cell.innerText.trim();
                    }
                    cellIdx++
                });
                visibleData.push(rowData);
            }
        });
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'export.php';
    form.target = '_blank';

    const params = {
        format: format,
        tabelle: php_tabelle,
        tabid: php_selectedTableID,
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

    // Add tableData as JSON if exporting filtered/visible rows
    if (!exportAll && visibleData.length > 0) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'tableData';
        input.value = JSON.stringify(visibleData);
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function setColumnFilter(field, value) {
    const input = document.querySelector(`.column-filter[data-field="${field}"]`);
    if (input) {
        // Wenn bereits gesetzt, dann entfernen
        if (input.value === value) {
            input.value = '';
            filterTableByColumns();
            return 'cleared';
        }
        input.value = value;
        filterTableByColumns();
        return true;
    }
    return false;
}

function filter_that(selectElement, typ){
    let selectedText = '';
    switch(typ){
        case 'select':
            selectedText = selectElement.options[selectElement.selectedIndex].text;
            break;
        case 'input':
            selectedText = selectElement.value;
            break;
        case 'div':
            selectedText = selectElement.innerHTML;
            break;
    }

    // Versuche, das data-field-Attribut zu lesen (Spaltenname)
    const field = selectElement.getAttribute && selectElement.getAttribute('data-field');
    const result = field && setColumnFilter(field, selectedText);
    if(result === true || result === 'cleared') {
        // Spaltenfilter wurde gesetzt oder entfernt, kein globaler Filter nötig
        event.preventDefault();
        return;
    }

    // Fallback: globaler Filter wie bisher
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

function filterTableByColumns() {
    const filters = {};
    document.querySelectorAll('.column-filter').forEach(input => {
        const value = input.value.trim();
        if (value) { 
            const field = input.dataset.field;
            const filterType = input.getAttribute('data-filtertyp') || 'TXT';
            console.log("Filtertyp: " + filterType);
            filters[field] = {
                value: value,
                type: filterType
            };
        }
    });
    const rows = document.querySelectorAll('table tbody tr');
    rows.forEach(row => {
        let show = true;
        for (const [field, filterInfo] of Object.entries(filters)) {
            const cell = row.querySelector(`td[data-field='${field}']`);
            if (!cell) continue;

            // Zellwert ermitteln
            let cellValue = '';
            const input = cell.querySelector('input');
            const select = cell.querySelector('select');
            if (input) {
                cellValue = input.value.trim();
            } else if (select) {
                cellValue = select.options[select.selectedIndex].text.trim();
            } else {
                cellValue = cell.textContent.trim();
            }
            // Je nach Filtertyp unterschiedlich filtern
            switch (filterInfo.type) {
                case 'NUM':
                    if (!matchesNumericFilter(cellValue, filterInfo.value)) {
                        show = false;
                    }
                    break;

                case 'DAT':
                    if (!matchesDateFilter(cellValue, filterInfo.value)) {
                        show = false;
                    }
                    break;

                case 'TXT':
                default:
                    if (!cellValue.toLowerCase().includes(filterInfo.value.toLowerCase())) {
                        show = false;
                    }
                    break;
            }

            if (!show) break;
        }
        row.style.display = show ? '' : 'none';
    });
}

// Hilfsfunktion für numerische Filter
function matchesNumericFilter(cellValue, filterValue) {
    // Zellwert als Zahl parsen
    const numValue = parseFloat(cellValue.replace(',', '.'));
    if (isNaN(numValue)) return false;

    // Bereichsfilter (x-y)
    if (filterValue.includes('-')) {
        const [min, max] = filterValue.split('-').map(v => parseFloat(v.replace(',', '.')));
        if (!isNaN(min) && !isNaN(max)) {
            return numValue >= min && numValue <= max;
        }
    }

    // Operatoren prüfen
    if (filterValue.startsWith('>=')) {
        const compareValue = parseFloat(filterValue.substring(2).replace(',', '.'));
        return !isNaN(compareValue) && numValue >= compareValue;
    }
    if (filterValue.startsWith('<=')) {
        const compareValue = parseFloat(filterValue.substring(2).replace(',', '.'));
        return !isNaN(compareValue) && numValue <= compareValue;
    }
    if (filterValue.startsWith('>')) {
        const compareValue = parseFloat(filterValue.substring(1).replace(',', '.'));
        return !isNaN(compareValue) && numValue > compareValue;
    }
    if (filterValue.startsWith('<>')) {
        const compareValue = parseFloat(filterValue.substring(2).replace(',', '.'));
        return !isNaN(compareValue) && numValue !== compareValue;
    }
    if (filterValue.startsWith('<')) {
        const compareValue = parseFloat(filterValue.substring(1).replace(',', '.'));
        return !isNaN(compareValue) && numValue < compareValue;
    }

    // Exakte Übereinstimmung
    const compareValue = parseFloat(filterValue.replace(',', '.'));
    return !isNaN(compareValue) && numValue === compareValue;
}

// Hilfsfunktion für Datumsfilter
function matchesDateFilter(cellValue, filterValue) {
    // Zelldatum parsen
    const cellDate = parseDate(cellValue);
    if (!cellDate) return false;

    // Bereichsfilter (x-y)
    if (filterValue.includes('-')) {
        const [startStr, endStr] = filterValue.split('-');
        const startDate = parseDate(startStr);
        const endDate = parseDate(endStr);
        
        if (startDate && endDate) {
            // Bei Bereichsfiltern setzen wir die Zeit für den Endtag auf 23:59:59
            if (endStr.length <= 10) // Wenn kein Zeitanteil vorhanden
                endDate.setHours(23, 59, 59, 999);
            
            return cellDate >= startDate && cellDate <= endDate;
        }
    }

    // Nur Jahreszahl eingegeben - exakte Jahresübereinstimmung
    if (/^\d{4}$/.test(filterValue)) {
        const year = parseInt(filterValue);
        return cellDate.getFullYear() === year;
    }

    // Operatoren prüfen - wichtig: Reihenfolge beachten (längere zuerst)
    if (filterValue.startsWith('>=')) {
        const compareDate = parseDate(filterValue.substring(2));
        return compareDate && cellDate >= compareDate;
    }
    if (filterValue.startsWith('<=')) {
        const compareDate = parseDate(filterValue.substring(2));
        if (!compareDate) return false;
        
        // Bei <= setzen wir die Zeit für den Vergleichstag auf 23:59:59
        if (filterValue.substring(2).length <= 10) { // Wenn kein Zeitanteil vorhanden
            compareDate.setHours(23, 59, 59, 999);
        }
        return cellDate <= compareDate;
    }
    if (filterValue.startsWith('<>')) {
        const compareDate = parseDate(filterValue.substring(2));
        if (!compareDate) return false;
        
        // Bei <> vergleichen wir nur das Datum, nicht die Uhrzeit, wenn kein Zeitanteil angegeben
        if (filterValue.substring(2).length <= 10) {
            return cellDate.getFullYear() !== compareDate.getFullYear() || 
                  cellDate.getMonth() !== compareDate.getMonth() || 
                  cellDate.getDate() !== compareDate.getDate();
        }
        return cellDate.getTime() !== compareDate.getTime();
    }
    if (filterValue.startsWith('>')) {
        // Spezialfall für Jahresangabe
        if (/^\d{4}$/.test(filterValue.substring(1))) {
            const year = parseInt(filterValue.substring(1));
            return cellDate.getFullYear() > year; // Wirklich größer, nicht gleich
        }
        
        const compareDate = parseDate(filterValue.substring(1));
        if (!compareDate) return false;
        
        return cellDate > compareDate;
    }
    if (filterValue.startsWith('<')) {
        // Spezialfall für Jahresangabe
        if (/^\d{4}$/.test(filterValue.substring(1))) {
            const year = parseInt(filterValue.substring(1));
            return cellDate.getFullYear() < year; // Wirklich kleiner, nicht gleich
        }
        
        const compareDate = parseDate(filterValue.substring(1));
        return compareDate && cellDate < compareDate;
    }

    // Prüfen auf Jahr mit <= oder >=
    if (filterValue.startsWith('<=') && /^\d{4}$/.test(filterValue.substring(2))) {
        const year = parseInt(filterValue.substring(2));
        return cellDate.getFullYear() <= year;
    }
    if (filterValue.startsWith('>=') && /^\d{4}$/.test(filterValue.substring(2))) {
        const year = parseInt(filterValue.substring(2));
        return cellDate.getFullYear() >= year;
    }

    // Exakte Übereinstimmung
    const compareDate = parseDate(filterValue);
    if (!compareDate) return false;
    
    // Bei exakten Vergleichen ohne Zeitanteil vergleichen wir nur das Datum
    if (filterValue.length <= 10) {
        return cellDate.getFullYear() === compareDate.getFullYear() && 
               cellDate.getMonth() === compareDate.getMonth() && 
               cellDate.getDate() === compareDate.getDate();
    }
    return cellDate.getTime() === compareDate.getTime();
}

// Hilfsfunktion zum Parsen verschiedener Datumsformate
function parseDate(dateStr) {
    if (!dateStr || typeof dateStr !== 'string') return null;
    dateStr = dateStr.trim();
    
    // ISO-Format: YYYY-MM-DD
    if (/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) {
        return new Date(dateStr);
    }
    
    // Deutsches Format: DD.MM.YYYY
    if (/^\d{1,2}\.\d{1,2}\.\d{4}$/.test(dateStr)) {
        const [day, month, year] = dateStr.split('.').map(Number);
        return new Date(year, month - 1, day);
    }
    
    // US-Format: MM/DD/YYYY
    if (/^\d{1,2}\/\d{1,2}\/\d{4}$/.test(dateStr)) {
        const [month, day, year] = dateStr.split('/').map(Number);
        return new Date(year, month - 1, day);
    }
    
    // ISO datetime: YYYY-MM-DDTHH:MM:SS
    if (/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}(:\d{2})?$/i.test(dateStr)) {
        return new Date(dateStr);
    }
    
    // SQL datetime: YYYY-MM-DD HH:MM:SS
    if (/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}(:\d{2})?$/i.test(dateStr)) {
        return new Date(dateStr.replace(' ', 'T'));
    }
    
    // Nur Jahr (für Vergleiche)
    if (/^\d{4}$/.test(dateStr)) {
        return new Date(parseInt(dateStr), 0, 1);
    }
    
    return null;
}

// ////////////////////////////////////////////////////////////////////////////////////////


function detectColumnDataTypes() {
    const table = document.querySelector('table');
    if (!table) return;
    
    // Alle Spaltenheader finden
    const headers = Array.from(table.querySelectorAll('thead th[data-field]'))
        .map(th => th.getAttribute('data-field'));
    
    // Für jede Spalte den Datentyp ermitteln
    headers.forEach(header => {
        if (header === 'id') return; // ID-Spalte überspringen
        
        const cells = Array.from(table.querySelectorAll(`tbody td[data-field='${header}']`));
        let isNumber = true;
        let isDate = true;
        let values = [];
        
        // Werte aus den Zellen sammeln
        cells.forEach(cell => {
            let value = ''; 
            const input = cell.querySelector('input');
            const select = cell.querySelector('select');
            
            if (input) {
                value = input.value.trim();
            } else if (select) {
                // Bei Select-Feldern den angezeigten Text verwenden, nicht den Value
                value = select.options[select.selectedIndex]?.text.trim() || '';
            } else {
                value = cell.textContent.trim();
            }
            if (value) {
                values.push(value);
            }
        });
      
        // Wenn keine Werte vorhanden, als TXT markieren
        if (values.length === 0) {
            setColumnFilterType(header, 'TXT');
            return;
        }
  
        // Prüfen, ob alle Werte Zahlen sind
        for (const value of values) {
            if (!/^-?\d+(\.\d+)?$/.test(value)) {
                isNumber = false;
            }
            
            // Prüfen, ob alle Werte Datumsformate sind
            if (!isDateFormat(value)) {
                isDate = false;
            }
            
            // Wenn weder Zahl noch Datum, sofort als TXT markieren und abbrechen
            if (!isNumber && !isDate) {
                break;
            }
        }
        
        // Datentyp setzen
        if (isNumber) {
            setColumnFilterType(header, 'NUM');
        } else if (isDate) {
            setColumnFilterType(header, 'DAT');
        } else {
            setColumnFilterType(header, 'TXT');
        }
    });
}

// Neue Funktion zum Setzen des Datentyps als data-filtertyp im Filterelement
function setColumnFilterType(header, type) {
    // Finde das Filterelement für diese Spalte
    const filterInput = document.querySelector(`.column-filter[data-field='${header}']`);
    if (filterInput) {
        // Setze den data-filtertyp
        filterInput.setAttribute('data-filtertyp', type);
        
        // Setze den Platzhaltertext je nach Datentyp
        switch(type) {
            case 'NUM':
                filterInput.placeholder = 'Numerischer Filter';
                break;
            case 'DAT':
                filterInput.placeholder = 'Datumsfilter';
                break;
            case 'TXT':
                filterInput.placeholder = 'Textfilter';
                break;
        }
        
        // Optional: Füge CSS-Klassen für visuelle Unterscheidung hinzu
        filterInput.classList.remove('filter-type-num', 'filter-type-dat', 'filter-type-txt');
        filterInput.classList.add(`filter-type-${type.toLowerCase()}`);
    }
}

// Hilfsfunktion zum Prüfen von Datumsformaten
function isDateFormat(value) {
    // Verschiedene Datumsformate prüfen
    const datePatterns = [
        /^\d{4}-\d{2}-\d{2}$/, // YYYY-MM-DD
        /^\d{2}\.\d{2}\.\d{4}$/, // DD.MM.YYYY
        /^\d{2}\/\d{2}\/\d{4}$/, // MM/DD/YYYY oder DD/MM/YYYY
        /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}(:\d{2})?$/, // ISO datetime
        /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}(:\d{2})?$/ // SQL datetime
    ];
    
    // Prüfen, ob der Wert einem der Datumsformate entspricht
    return datePatterns.some(pattern => pattern.test(value));
}

