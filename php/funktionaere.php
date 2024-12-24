<?php
$admin = 1;
$tabelle = "funktionaere";
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Funktionäre bearbeiten</title>

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
                            if (response.status === "success") {
                                checkRow(tabelle, id, field, value);  // Feld und Wert an check übergeben
                            }
                        } else {
                            alert("Fehler beim Update: " + response.message);
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
            const xhr = new XMLHttpRequest();  // XMLHttpRequest innerhalb der Funktion definieren
            xhr.open("POST", "ajax.php", true);
            xhr.setRequestHeader("Content-Type", "application/json");

            // Die Daten, die an ajax.php gesendet werden
            const data = JSON.stringify({
                action: 'check',
                tabelle: tabelle,
                id: id,
                field: field,
                value: value
            });

            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4) {
                    console.log("Antwort von ajax.php:", xhr.responseText);  // Debugging: Rohantwort anzeigen
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.status === "success") {
                                const dbRow = response.row;
                                updateRowColors(dbRow, id);
                            } else {
                                alert("Fehler beim Abgleich der Zeile: " + response.message);
                            }
                        } catch (e) {
                            alert("Fehler beim Verarbeiten der Serverantwort.");
                        }
                    } else {
                        alert("Serverfehler beim Update.");
                    }
                }
            };

            xhr.send(data);  // Senden der Anfrage an ajax.php
        }



        function updateRowColors(dbRow, id) {
            const row = document.querySelector(`tr[data-id='${id}']`);
            if (row) {
                row.querySelectorAll('td[data-field]').forEach(td => {
                    const field = td.getAttribute('data-field');
                    const input = td.querySelector('input');

                    if (input) {
                        // Wenn der Feldwert nicht übereinstimmt, rot färben
                        if (dbRow[field] == input.value.trim()) {
                            td.style.backgroundColor = 'lightgreen';
                        } else {
                            td.style.backgroundColor = 'lightcoral';
                            input.value = dbRow[field];  // DB-Wert übernehmen
                        }
                    }
                });
            }
        }
    </script>
</head>
<body>
    <div class="container mt-4">
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <?php if (!empty($data)): ?>
                        <?php foreach (array_keys($data[0]) as $header): ?>
                            <th><?= htmlspecialchars($header) ?></th>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $row): ?>
                    <tr data-id="<?= $row['id'] ?>">
                        <?php foreach ($row as $key => $value): ?>
                            <td data-field="<?= $key ?>">
                                <?php if ($admin && strcasecmp($key, 'id') !== 0): ?>
                                    <input type="text" class="form-control"
                                           value="<?= htmlspecialchars($value) ?>"
                                           onchange="updateField('<?= $tabelle ?>', '<?= $row['id'] ?>', '<?= $key ?>', this.value)">
                                <?php else: ?>
                                    <?= htmlspecialchars($value) ?>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
