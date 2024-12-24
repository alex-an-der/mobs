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
        // Funktion zur Verarbeitung von AJAX-Updates
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
                    console.log("Update erfolgreich");
                } else if (xhr.readyState === 4) {
                    console.error("Fehler beim Update: " + xhr.responseText);
                }
            };

            xhr.send(data);
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
                            <?php if (strcasecmp($header, 'id') !== 0): ?>
                                <th><?= htmlspecialchars($header) ?></th>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $row): ?>
                    <tr>
                        <?php 
                        $record_id = null;
                        foreach ($row as $key => $value): 
                            if (strcasecmp($key, 'id') === 0) {
                                $record_id = $value;
                            }
                        ?>
                            <td <?php if (strcasecmp($key, 'id') === 0) echo 'style="display:none;"'; ?>>
                                <?php if ($admin && strcasecmp($key, 'id') !== 0): ?>
                                    <input type="text" class="form-control" 
                                        value="<?= htmlspecialchars($value) ?>" 
                                        onchange="updateField('<?= $tabelle ?>', '<?= $record_id ?>', '<?= $key ?>', this.value)">
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
