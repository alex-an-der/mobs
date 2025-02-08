<?php
require_once(__DIR__ . "/user_includes/all.head.php");
require_once(__DIR__ . "/inc/include.php");
require_once(__DIR__ . "/config/config.php");

// Hole Statistik-Auswahl aus config.php
$selectedStat = isset($_GET['stat']) ? (int)$_GET['stat'] : 0;
if (!isset($statistik[$selectedStat])) {
    $selectedStat = 0;
}

// Daten für die ausgewählte Statistik holen
$stat = $statistik[$selectedStat];
$result = $db->query($stat['query']);
$data = isset($result['data']) ? $result['data'] : [];

// Sortiere die Daten nach Werten (zweite Spalte) absteigend
if (!empty($data)) {
    $valueKey = array_keys($data[0])[1];
    usort($data, function($a, $b) use ($valueKey) {
        return $b[$valueKey] <=> $a[$valueKey];
    });
}

// Labels und Werte für Chart.js vorbereiten
$labels = !empty($data) ? array_column($data, array_keys($data[0])[0]) : [];
$values = !empty($data) ? array_column($data, array_keys($data[0])[1]) : [];

// Rest des Codes bleibt gleich...
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=TITEL?> - Statistik</title>
    <!-- Chart.js für die Diagramme -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function goBack() {
            window.location.href = 'index.php';
        }
    </script>
    <style>
            body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background-image: url('./inc/img/body_green.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <!-- Zurück-Button wie im Importeur -->
        <form method="get" class="d-flex align-items-center mb-4">
            <select name="stat" class="form-control me-2" onchange="this.form.submit()">
                <?php foreach ($statistik as $index => $stat): ?>
                    <option value="<?= $index ?>" <?= $selectedStat == $index ? 'selected' : '' ?>>
                        <?= htmlspecialchars($stat['titel']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <a href="index.php" class="btn btn-secondary">Zurück</a>
        </form>

        <!-- Diagramm-Container -->
        <div class="card">
            <div class="card-body">
                <!-- Export Button -->
                <div class="text-end mb-3">
                    <button class="btn btn-info" onclick="exportStatistik()">Als PDF exportieren</button>
                </div>
                <!-- Remove the duplicate title below -->
                <div style="width: 66%; margin: auto;">
                    <canvas id="chartContainer"></canvas>
                </div>
                
                <!-- Tabelle mit sortierten Werten -->
                <?php if (!empty($data)): ?>
                    <table class="table table-sm mt-4">
                        <thead>
                            <tr>
                                <th><?= htmlspecialchars(array_keys($data[0])[0]) ?></th>
                                <th class="text-right"><?= htmlspecialchars(array_keys($data[0])[1]) ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($data as $row): 
                                $label = array_values($row)[0];
                                $value = array_values($row)[1];
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($label) ?></td>
                                    <td class="text-right"><?= htmlspecialchars($value) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Exportfunktion
        function exportStatistik() {
            // Chart als Base64-String holen
            const canvas = document.getElementById('chartContainer');
            const chartImage = canvas.toDataURL('image/png');

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'export_statistik.php';
            form.target = '_blank';

            const params = {
                stat: '<?= $selectedStat ?>',
                format: 'pdf',
                chartImage: chartImage  // Hier fügen wir das Bild hinzu
            };

            for (const key in params) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = params[key];
                form.appendChild(input);
            }

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }

        // Diagramm erstellen
        const ctx = document.getElementById('chartContainer').getContext('2d');
        
        <?php
        $stat = $statistik[$selectedStat];
        // Daten für Chart.js aufbereiten
        
        $colorPalette =[
        '#383838', // Anthrazit
        '#ff69b4', // Pink
        '#8a8a8a', // warmes grau
        '#7F0000', // Bordeaux rot
        '#FFB6C1', // zartes Rosa
        '#FFD700', // Gold
        '#1b0d94', // Dunkelblau
        '#FF5733', // Lebendiges Orange
        '#57FF33', // Limettengrün
        '#3357FF', // Blau
        '#FF33F2', // Fuchsia
        '#33FFF2'  // Türkis
        ];

        $colors = [];
        $availableColors = $colorPalette;
        $numColors = count($labels);

        for ($i = 0; $i < $numColors; $i++) {
            if (empty($availableColors)) {
                $availableColors = $colorPalette;
            }
            $randomIndex = array_rand($availableColors);
            $colors[] = $availableColors[$randomIndex];
            unset($availableColors[$randomIndex]);
        }

        
        echo "const data = {
            labels: " . json_encode($labels) . ",
            datasets: [{
                data: " . json_encode($values) . ",
                backgroundColor: " . json_encode($colors) . "
            }]
        };";
        
        if ($stat['typ'] === 'torte') {
            echo "
            new Chart(ctx, {
                type: 'pie',
                data: data,
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'right',
                        },
                        title: {
                            display: true,
                            text: " . json_encode($stat['titel']) . ",
                            font: {
                                size: 24
                            }
                        },
                        tooltip: {
                            titleFont: {
                                size: 16
                            },
                            bodyFont: {
                                size: 16
                            },
                            padding: 16
                        }
                    }
                }
            });";
        }
        ?>
    </script>
</body>
</html>
