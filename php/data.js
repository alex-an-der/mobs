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
                    alert("Fehler beim Update. Stimmt das Datenformat? Für Details siehe log-Tabelle in der Datenbank.");
                }
            } catch (e) {
                markCellError(id, field);
                alert("Fehler beim Verarbeiten der Serverantwort.");
            }
        } else if (xhr.readyState === 4 && xhr.status !== 200) {
            markCellError(id, field);
            alert("Serverfehler beim Update. Stimmt das Datenformat? Für Details siehe log-Tabelle in der Datenbank.");
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
        const bInput = bCell.querySelector('input, select');

        if (aInput) {
            if (aInput.tagName.toLowerCase() === 'select') {
                aText = aInput.options[aInput.selectedIndex].text.trim();
            } else {
                aText = aInput.value.trim();
            }
        }

        if (bInput) {
            if (bInput.tagName.toLowerCase() === 'select') {
                bText = bInput.options[bInput.selectedIndex].text.trim();
            } else {
                bText = bInput.value.trim();
            }
        }

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
