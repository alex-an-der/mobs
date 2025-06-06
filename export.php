<?php
// Aktiviere Output Buffering am Anfang der Datei
ob_start();

// Fehlerausgabe aktivieren
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Add to your config file
ini_set('log_errors', 1);
ini_set('error_log', './../mobs_error.log');

// Validate input
$format = $_POST['format'] ?? '';
$tabelle = $_POST['tabelle'] ?? '';
$tabid = $_POST['tabid'] ?? '';
$exportAll = ($_POST['exportAll'] ?? '1') === '1';
$ids = $_POST['ids'] ?? '';
$tableDataJson = $_POST['tableData'] ?? '';
error_log('Export-IDs (POST): ' . $ids);

// Nur für Formate, die HTML benötigen, Includes laden
if (!in_array($format, ['csv', 'excel'])) {
    require_once(__DIR__ . "/user_includes/all.head.php");
}
require_once(__DIR__ . "/inc/include.php");

if (!$tabelle || !isset($anzuzeigendeDaten[$tabid])) {
    die('Invalid parameters');
}

// Bestimme den Anzeigenamen für die Datei
$displayName = $anzuzeigendeDaten[$tabid]['auswahltext'] ?? $tabelle;
$filename = str_replace(' ', '_', $displayName);

// --- WYSIWYG Export: Use posted tableData if present ---
if (!$exportAll && !empty($tableDataJson)) {
    $data = json_decode($tableDataJson, true);
    if (!is_array($data) || empty($data)) {
        die('No data available for export');
    }
    // Skip SQL query, go directly to export
    // (Foreign key display values are already in the table, so no need to process again)
} else {
    // Get data
    $query = $anzuzeigendeDaten[$tabid]['query'];
    if (!$exportAll && !empty($ids)) {
        $idArray = explode(',', $ids);
        $idList = implode(',', array_map('intval', $idArray));
        
        error_log("Original query: $query");
        error_log("Filtered IDs: $idList");
        
        $tableInfo = extractTableInfo($query);
        $idField = $tableInfo['idField'];
        error_log("Using ID field: $idField for filtered query");

        // Neue, robustere Logik:
        $idFilter = "$idField IN ($idList)";
        $hasWhere = stripos($query, 'WHERE') !== false;
        $hasGroupBy = stripos($query, 'GROUP BY') !== false;
        $hasOrderBy = stripos($query, 'ORDER BY') !== false;

        if ($hasWhere) {
            // Füge AND id IN (...) vor GROUP BY/ORDER BY/Ende ein
            if ($hasGroupBy) {
                $query = preg_replace('/(WHERE .+?)(GROUP BY)/is', '$1 AND ' . $idFilter . ' $2', $query, 1);
            } else if ($hasOrderBy) {
                $query = preg_replace('/(WHERE .+?)(ORDER BY)/is', '$1 AND ' . $idFilter . ' $2', $query, 1);
            } else {
                $query .= ' AND ' . $idFilter;
            }
        } else {
            // Füge WHERE id IN (...) vor GROUP BY/ORDER BY/Ende ein
            if ($hasGroupBy) {
                $query = preg_replace('/(GROUP BY)/i', 'WHERE ' . $idFilter . ' $1', $query, 1);
            } else if ($hasOrderBy) {
                $query = preg_replace('/(ORDER BY)/i', 'WHERE ' . $idFilter . ' $1', $query, 1);
            } else {
                $query .= ' WHERE ' . $idFilter;
            }
        }
        error_log("Modified filtered query: $query");
    }

    // Execute query with error reporting
    try {
        error_log("Executing query: " . substr($query, 0, 500) . (strlen($query) > 500 ? "..." : ""));
        $result = $db->query($query);
        
        // Log result information for debugging
        if (isset($result['data'])) {
            error_log("Query returned " . count($result['data']) . " rows");
            if (count($result['data']) > 0) {
                $sample = json_encode(array_slice($result['data'], 0, 1));
                error_log("Sample data: $sample");
            }
        } else {
            error_log("Query returned no data array");
            if (isset($result['error'])) {
                error_log("Database error: " . $result['error']);
            }
        }
    } catch (Exception $e) {
        error_log("Exception in database query: " . $e->getMessage());
        if ($format === 'maillist') {
            $result = ['data' => []];
        } else {
            die('Database error: ' . $e->getMessage());
        }
    }

    // Verbesserte Fehlerbehandlung
    if (!isset($result['data']) || !is_array($result['data']) || empty($result['data'])) {
        if ($format === 'maillist') {
            // Für Maillist-Format zeige leere Liste an, anstatt zu sterben
            $data = [];
        } else {
            die('No data available');
        }
    } else {
        $data = $result['data'];
    }

    // Process foreign key references
    $FKdata = array();
    if (isset($anzuzeigendeDaten[$tabid]['referenzqueries'])) {
        foreach ($anzuzeigendeDaten[$tabid]['referenzqueries'] as $SRC_ID => $query) {
            $result = $db->query($query);
            if (isset($result['data'])) {
                $FKdata[$SRC_ID] = array_column($result['data'], 'anzeige', 'id');
            }
        }
    }

    // Replace foreign keys with their display values
    if (!empty($data)) {
        foreach ($data as &$row) {
            foreach ($row as $key => &$value) {
                if (isset($FKdata[$key]) && isset($FKdata[$key][$value])) {
                    $value = $FKdata[$key][$value];
                }
            }
        }
    }

    // Sortierparameter aus Session holen (müssen vorher in index.php gespeichert werden)
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $sortColumn = $_SESSION['sortColumn'] ?? null;
    $sortOrder = $_SESSION['sortOrder'] ?? 'asc';

    // Daten sortieren
    if (!empty($data)) {
        $data = sortData($data, $sortColumn, $sortOrder);
    }
}

// Export based on format
switch ($format) {
    case 'pdf':
        if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
            die('Composer autoload.php nicht gefunden. Bitte führen Sie "composer install" aus.');
        }
        require_once(__DIR__ . '/vendor/autoload.php');
        if (!class_exists('TCPDF')) {
            die('TCPDF Klasse nicht gefunden. Bitte installieren Sie das Paket mit "composer require tecnickcom/tcpdf"');
        }
        exportPDF($data, $tabelle);
        break;
    case 'excel':
        if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
            die('Composer autoload.php nicht gefunden. Bitte führen Sie "composer install" aus.');
        }
        require_once(__DIR__ . '/vendor/autoload.php');
        if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            die('PhpSpreadsheet Klasse nicht gefunden. Bitte installieren Sie das Paket mit "composer require phpoffice/phpspreadsheet"');
        }
        exportExcel($data, $tabelle);
        break;
    case 'csv':
        exportCSV($data, $tabelle);
        break;
    case 'maillist':
        exportMailList($data, $tabelle);
        break;
    case 'markdown':
        exportMarkdown($data, $tabelle);
        break;
    default:
        die('Invalid format');
}

// Modifiziere die PDF-Export-Funktion für bessere Fehlerbehandlung
function exportPDF($data, $tabelle) {
    global $filename;
    try {
        ob_clean();
        
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // PDF Metadaten und Einstellungen
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('LBSV Niedersachsen');
        $pdf->SetTitle($filename);
        
        // Kopf- und Fußzeile
        $pdf->setHeaderFont(Array('helvetica', '', 10));
        $pdf->setFooterFont(Array('helvetica', '', 8));
        $pdf->SetHeaderData('', 0, TITEL, $filename . "\nExportiert am: " . date('d.m.Y H:i'));
        
        // Seitenränder
        $pdf->SetMargins(15, 27, 15);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(10);
        
        // Automatische Seitenumbrüche
        $pdf->SetAutoPageBreak(TRUE, 25);
        
        $pdf->AddPage('L');  // Querformat
        $pdf->SetFont('helvetica', '', 9);  // Etwas größere Schrift
        
        // Berechne verfügbare Breite
        $pageWidth = $pdf->getPageWidth();
        $margins = $pdf->getMargins();
        $availableWidth = $pageWidth - $margins['left'] - $margins['right'];
        
        // Tabellen-Styling
        $html = '<style>
            table {
                border-collapse: collapse;
                width: 100%;
                margin-bottom: 10px;
            }
            th {
                background-color: #f5f5f5;
                font-weight: bold;
                text-align: left;
                padding: 8px;
                border: 1px solid #ddd;
            }
            td {
                padding: 6px 8px;
                border: 1px solid #ddd;
                vertical-align: top;
            }
            tr:nth-child(even) {
                background-color: #f9f9f9;
            }
            .wrap-text {
                word-break: break-word;
                white-space: pre-line;
            }
        </style>';
        
        // Tabellenkopf
        $html .= '<table cellpadding="4">';
        $headers = array_keys($data[0]);
        $visibleHeaders = array_filter($headers, function($header) {
            return strcasecmp($header, 'id') !== 0;
        });

        // Automatische Erkennung schmaler und Textspalten
        $narrowColumns = [];
        $textColumns = [];
        foreach ($visibleHeaders as $header) {
            // Schmale Spalten: sehr kurze Überschrift und alle Werte sind kurz (<=5 Zeichen)
            $isNarrow = (mb_strlen($header) <= 5);
            if ($isNarrow) {
                $allShort = true;
                foreach ($data as $row) {
                    if (mb_strlen((string)($row[$header] ?? '')) > 5) {
                        $allShort = false;
                        break;
                    }
                }
                if ($allShort) {
                    $narrowColumns[] = $header;
                    continue;
                }
            }
            // Textspalten: mindestens ein Wert ist lang (>=30 Zeichen) oder enthält Zeilenumbrüche
            $isText = false;
            foreach ($data as $row) {
                $val = (string)($row[$header] ?? '');
                if (mb_strlen($val) >= 30 || strpos($val, "\n") !== false) {
                    $isText = true;
                    break;
                }
            }
            if ($isText) {
                $textColumns[] = $header;
            }
        }

        // Spaltenbreiten berechnen
        $columnWidths = [];
        $totalContentWidth = 0;
        $minNarrow = 18; // Mindestbreite für schmale Spalten (mm)
        $maxNarrow = 28; // Maximalbreite für schmale Spalten (mm)
        $minText = 40;   // Mindestbreite für Textspalten (mm)
        $maxText = 90;   // Maximalbreite für Textspalten (mm)
        $default = 28;   // Standardbreite für andere Spalten (mm)

        // 1. Vorbelegen mit Mindestwerten
        foreach ($visibleHeaders as $header) {
            $headerClean = strtolower(str_replace('info:', '', $header));
            if (in_array($header, $narrowColumns) || in_array($headerClean, $narrowColumns)) {
                $columnWidths[$header] = $minNarrow;
            } elseif (in_array($header, $textColumns) || in_array($headerClean, $textColumns)) {
                $columnWidths[$header] = $minText;
            } else {
                $columnWidths[$header] = $default;
            }
        }

        // 2. Passe Breite anhand Inhalt an (aber nicht unter Mindestwert)
        foreach ($visibleHeaders as $header) {
            $maxWidth = strlen($header) * 2.5;
            foreach ($data as $row) {
                $maxWidth = max($maxWidth, strlen($row[$header]) * 2.2);
            }
            $headerClean = strtolower(str_replace('info:', '', $header));
            if (in_array($header, $narrowColumns) || in_array($headerClean, $narrowColumns)) {
                $columnWidths[$header] = max($minNarrow, min($maxWidth, $maxNarrow));
            } elseif (in_array($header, $textColumns) || in_array($headerClean, $textColumns)) {
                $columnWidths[$header] = max($minText, min($maxWidth, $maxText));
            } else {
                $columnWidths[$header] = max($default, min($maxWidth, 40));
            }
        }

        // 3. Gesamtbreite berechnen und ggf. Textspalten flexibel anpassen
        $totalContentWidth = array_sum($columnWidths);
        if ($totalContentWidth > $availableWidth) {
            // Skaliere nur Textspalten, andere bleiben bei Mindestbreite
            $fixedWidth = 0;
            $textCols = [];
            foreach ($visibleHeaders as $header) {
                $headerClean = strtolower(str_replace('info:', '', $header));
                if (in_array($header, $textColumns) || in_array($headerClean, $textColumns)) {
                    $textCols[] = $header;
                } else {
                    $fixedWidth += $columnWidths[$header];
                }
            }
            $remainingWidth = max($availableWidth - $fixedWidth, count($textCols) * $minText);
            $sumText = 0;
            foreach ($textCols as $header) {
                $sumText += $columnWidths[$header];
            }
            foreach ($textCols as $header) {
                $columnWidths[$header] = max($minText, ($columnWidths[$header] / $sumText) * $remainingWidth);
            }
        }

        // Header-Zeile
        $html .= '<tr>';
        foreach ($visibleHeaders as $header) {
            $displayHeader = $header;
            if (strpos($header, 'info:') === 0) {
                $displayHeader = substr($header, 5);
            }
            $html .= sprintf(
                '<th style="width:%.1fmm">%s</th>',
                $columnWidths[$header],
                htmlspecialchars($displayHeader)
            );
        }
        $html .= '</tr>';
        
        // Datenzeilen
        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($row as $key => $value) {
                if (strcasecmp($key, 'id') !== 0) {
                    // Prüfe auf Datum im Format yyyy-mm-dd
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                        $dateObj = DateTime::createFromFormat('Y-m-d', $value);
                        if ($dateObj) {
                            $value = $dateObj->format('d.m.Y');
                        }
                    }
                    // Textspalten mit Umbruch-Style
                    $keyClean = strtolower(str_replace('info:', '', $key));
                    $isText = in_array($key, $textColumns) || in_array($keyClean, $textColumns);
                    $tdClass = $isText ? ' class="wrap-text"' : '';
                    $html .= sprintf(
                        '<td style="width:%.1fmm"%s>%s</td>',
                        $columnWidths[$key],
                        $tdClass,
                        htmlspecialchars($value)
                    );
                }
            }
            $html .= '</tr>';
        }
        $html .= '</table>';
        
        $pdf->writeHTML($html, true, false, true, false, '');
        
        ob_end_clean();
        $pdf->Output($filename . '.pdf', 'D');
    } catch (Exception $e) {
        die('PDF Export Fehler: ' . $e->getMessage());
    }
}

// Modifiziere die Excel-Export-Funktion für bessere Fehlerbehandlung
function exportExcel($data, $tabelle) {
    global $filename;
    try {
        if (empty($data)) die('Keine Daten zum Exportieren vorhanden');

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Headers ohne ID
        $headers = array_filter(array_keys($data[0]), function($header) {
            return strcasecmp($header, 'id') !== 0;
        });
        $headers = array_values($headers);
        
        // Alle Spalten als Text formatieren, um Datumsumwandlung zu verhindern
        $columnFormats = [];
        foreach ($headers as $header) {
            $columnFormats[$header] = ['type' => 'text', 'format' => '@'];
        }
        
        // Headers schreiben und formatieren
        foreach ($headers as $colIndex => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValueExplicit($colLetter . '1', $header, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->getStyle($colLetter . '1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 11],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F5F5F5']
                ]
            ]);
            // Spaltenformat auf Text setzen
            $sheet->getStyle($colLetter . '2:' . $colLetter . (count($data) + 1))
                ->getNumberFormat()
                ->setFormatCode('@');
        }
        
        // Daten schreiben
        $rowIndex = 2;
        foreach ($data as $rowData) {
            $colIndex = 1;
            foreach ($headers as $header) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                $value = $rowData[$header] ?? '';
                // Immer als Text setzen
                $sheet->setCellValueExplicit($colLetter . $rowIndex, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $colIndex++;
            }
            $rowIndex++;
        }
        
        // Intelligente Spaltenbreite
        foreach ($headers as $colIndex => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            
            // Maximale Breite basierend auf Inhalt (mit 10% Extra)
            $maxLength = strlen($header);
            foreach ($data as $row) {
                $maxLength = max($maxLength, strlen($row[$header]));
            }
            
            // Breite in Zeichen (mit Minimum und Maximum)
            $width = min(max($maxLength * 1.1, 8), 50);
            $sheet->getColumnDimension($colLetter)->setWidth($width);
        }

        // Format bestimmen und Writer erstellen
        $requestedFormat = isset($_POST['spreadsheet_format']) ? $_POST['spreadsheet_format'] : 'Xlsx';
        $format = in_array($requestedFormat, ['Xlsx', 'Ods']) ? $requestedFormat : 'Xlsx';
        
        // Mime-Types
        $mimeTypes = [
            'Xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Ods'  => 'application/vnd.oasis.opendocument.spreadsheet'
        ];
        
        // Buffer leeren und Headers setzen
        ob_end_clean();
        
        header('Content-Type: ' . $mimeTypes[$format]);
        header('Content-Disposition: attachment;filename="' . $filename . '.' . strtolower($format) . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Writer erstellen und speichern
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, $format);
        $writer->save('php://output');
        
        // Beenden um weitere Ausgaben zu verhindern
        exit();
        
    } catch (Exception $e) {
        error_log('Excel Export Error: ' . $e->getMessage());
        die('Excel Export Fehler: ' . $e->getMessage());
    }
}

function exportCSV($data, $tabelle) {
    global $filename;
    ob_clean(); // Buffer leeren, damit keine HTML-Ausgaben im CSV landen
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename . '.csv');
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
    
    // Headers
    $headers = array_keys($data[0]);
    fputcsv($output, $headers, ';');
    
    // Data
    foreach ($data as $row) {
        $csvRow = [];
        foreach ($headers as $header) {
            $value = isset($row[$header]) ? $row[$header] : '';
            // Prüfe, ob Wert numerisch ist (aber nicht mit führender Null)
            $isNumeric = is_numeric($value) && !preg_match('/^0[0-9]/', $value);
            if ($isNumeric || $value === '' || $value === null) {
                $csvRow[] = $value;
            } else {
                // Maskiere Anführungszeichen im Text
                $escaped = str_replace('"', '""', $value);
                $csvRow[] = '"' . str_replace('"', '""', $value) . '"';
            }
        }
        // Schreibe die Zeile manuell, damit keine automatische Maskierung von fputcsv erfolgt
        fwrite($output, implode(';', $csvRow) . "\n");
    }
    
    fclose($output);
}

// Nach Get Data und vor Export Format, neue Sortierfunktion hinzufügen:
function sortData($data, $sortColumn = null, $sortOrder = 'asc') {
    if (!$sortColumn) return $data;
    
    usort($data, function($a, $b) use ($sortColumn, $sortOrder) {
        $aVal = $a[$sortColumn] ?? '';
        $bVal = $b[$sortColumn] ?? '';
        
        // Numerische Werte erkennen und vergleichen
        if (is_numeric(str_replace(',', '.', $aVal)) && is_numeric(str_replace(',', '.', $bVal))) {
            $aNum = floatval(str_replace(',', '.', $aVal));
            $bNum = floatval(str_replace(',', '.', $bVal));
            return $sortOrder === 'asc' ? $aNum - $bNum : $bNum - $aNum;
        }
        
        // String Vergleich
        $compare = strcasecmp($aVal, $bVal);
        return $sortOrder === 'asc' ? $compare : -$compare;
    });
    
    return $data;
}

// Neue Hilfsfunktion zum Erkennen des Spaltenformats
function detectColumnFormat($data, $header) {
    $format = ['type' => 'text', 'format' => '@'];
    $allNumeric = true;
    $allDates = true;
    
    foreach ($data as $row) {
        $value = $row[$header] ?? '';
        if (empty($value)) continue;
        
        // Prüfe auf Datum
        $timestamp = strtotime($value);
        if ($timestamp === false) {
            $allDates = false;
        }
        
        // Prüfe auf Zahl
        if (!is_numeric(str_replace(',', '.', $value))) {
            $allNumeric = false;
        }
        
        if (!$allDates && !$allNumeric) break;
    }
    
    if ($allDates) {
        $format['type'] = 'date';
        $format['format'] = 'DD.MM.YYYY';
    } elseif ($allNumeric) {
        $format['type'] = 'number';
        // Prüfe auf Dezimalstellen
        $hasDecimals = false;
        foreach ($data as $row) {
            $value = str_replace(',', '.', $row[$header] ?? '');
            if (strpos($value, '.') !== false) {
                $hasDecimals = true;
                break;
            }
        }
        $format['format'] = $hasDecimals ? '#,##0.00' : '#,##0';
    }
    
    return $format;
}

// Neue Funktion für den Export von Mail-Verteilerlisten
function exportMailList($data, $tabelle) {
    global $filename, $exportAll, $ids, $db, $query;
    
    // Debug information
    error_log("exportMailList called with " . count($data) . " records");
    error_log("exportAll = " . ($exportAll ? "true" : "false") . ", ids = $ids");
    
    // Überprüfe, ob wir bei gefilterten Daten tatsächlich die richtigen Daten haben
    if (!$exportAll && empty($data)) {
        error_log("No data found for filtered export. Original query: $query");
    }
    
    // Buffer leeren und Headers setzen
    ob_end_clean();
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: inline; filename=' . $filename . '_Mailverteiler.html');
    
    // Gehe durch alle Spalten und prüfe auf E-Mail-Adressen
    $emailColumns = [];
    $emailCount = 0;
    
    // Hole alle Spaltenüberschriften - mit Fehlerprüfung
    if (empty($data)) {
        $headers = [];
        error_log("No data available for mail list");
    } else {
        $headers = array_keys($data[0]);
        error_log("Processing columns: " . implode(", ", $headers));
    }
    
    // First, scan all data to find potential email columns
    $potentialEmailColumns = [];
    foreach ($headers as $header) {
        foreach ($data as $row) {
            $value = isset($row[$header]) && $row[$header] !== null ? (string)$row[$header] : '';
            $value = trim($value);
            
            if (!empty($value) && filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $potentialEmailColumns[$header] = true;
                break;
            }
        }
    }
    
    error_log("Potential email columns found: " . implode(", ", array_keys($potentialEmailColumns)));
    
    // Now process all email columns we found
    foreach (array_keys($potentialEmailColumns) as $header) {
        $validEmails = [];
        
        // Prüfe alle Zeilen in dieser Spalte
        foreach ($data as $row) {
            $value = isset($row[$header]) && $row[$header] !== null ? (string)$row[$header] : '';
            $value = trim($value);
            
            // Prüfe, ob die Zelle eine gültige E-Mail-Adresse enthält
            if (!empty($value) && filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $validEmails[] = $value;
                $emailCount++;
            }
        }
        
        // Wenn die Spalte E-Mail-Adressen enthält, speichere sie
        if (!empty($validEmails)) {
            // Entferne Duplikate
            $validEmails = array_unique($validEmails);
            $emailColumns[$header] = $validEmails;
        }
    }
    
    error_log("Total valid emails found: $emailCount");
    error_log("Email columns with valid emails: " . count($emailColumns));
    
    // Dynamischer Titel basierend auf der Anzahl der gefundenen Mailinglisten
    $pageTitle = count($emailColumns) === 1 ? 
        "Mailingliste der Tabellenansicht" : 
        "Mailinglisten der Tabellenansicht";
        
    if (!$exportAll) {
        $pageTitle = count($emailColumns) === 1 ? 
            "Mailingliste der gefilterten Tabellenansicht" : 
            "Mailinglisten der gefilterten Tabellenansicht";
    }
    
    echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>' . $pageTitle . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        h2 { color: #555; margin-top: 20px; }
        .email-list { 
            background-color: #f5f5f5; 
            padding: 10px; 
            border-radius: 5px;
            word-wrap: break-word;
            margin-bottom: 20px;
        }
        .no-emails {
            color: #999;
            font-style: italic;
        }
        .copy-button {
            background-color: #4CAF50;
            border: none;
            color: white;
            padding: 8px 16px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 4px;
        }
        .close-button {
            background-color: #f44336;
            border: none;
            color: white;
            padding: 8px 16px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 4px;
            position: absolute;
            top: 20px;
            right: 20px;
        }
        .filtered-info {
            background-color: #FFF3CD;
            color: #856404;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
    <script>
        function copyToClipboard(elementId) {
            const element = document.getElementById(elementId);
            if (!element) return;
            
            const text = element.innerText;
            navigator.clipboard.writeText(text).then(
                function() {
                    // Erfolg: Zeige Feedback für den Benutzer
                    alert("E-Mail-Adressen wurden in die Zwischenablage kopiert!");
                }, 
                function() {
                    // Fehler: Bitte den Benutzer, es manuell zu kopieren
                    alert("Kopieren fehlgeschlagen. Bitte markieren Sie die Adressen und kopieren Sie manuell.");
                }
            );
        }
        
        function closePage() {
            window.close();
        }
    </script>
</head>
<body>
    <button class="close-button" onclick="closePage()">Seite schließen</button>
    <h1>' . $pageTitle . '</h1>';

    // Info anzeigen, wenn gefilterte Daten vorhanden sind
    if (!$exportAll && !empty($ids)) {
        echo '<div class="filtered-info">
            <strong>Hinweis:</strong> Es werden nur E-Mail-Adressen aus den gefilterten Datensätzen angezeigt.
            <br>Anzahl der Datensätze: ' . count($data) . '
        </div>';
    }

    // Wenn keine E-Mail-Spalten gefunden wurden
    if (empty($emailColumns)) {
        if (empty($data)) {
            echo '<p class="no-emails">Es wurden keine Datensätze zur Anzeige gefunden.</p>';
        } else {
            echo '<p class="no-emails">Es wurden keine E-Mail-Adressen in den ' . count($data) . ' gefilterten Datensätzen gefunden.</p>';
            
            // Debugging für Benutzer anzeigen
            echo '<div class="filtered-info">
                <strong>Debug-Info:</strong><br>
                Verfügbare Spalten: ' . implode(', ', $headers) . '<br>
                Prüfen Sie, ob die E-Mail-Spalte korrekt formatierte E-Mail-Adressen enthält.
            </div>';
        }
    } else {
        // Für jede E-Mail-Spalte die Verteilerliste ausgeben
        foreach ($emailColumns as $header => $emails) {
            $uniqueId = 'email-list-' . md5($header);
            echo '<h2>' . htmlspecialchars($header) . ' <button class="copy-button" onclick="copyToClipboard(\'' . $uniqueId . '\')">Kopieren</button></h2>';
            echo '<div id="' . $uniqueId . '" class="email-list">';
            echo implode('; ', $emails);
            echo '</div>';
            
            // Zeige die Anzahl der eindeutigen E-Mail-Adressen an
            echo '<p>' . count($emails) . ' eindeutige E-Mail-Adressen gefunden.</p>';
        }
    }
    
    echo '</body>
</html>';
    
    exit();
}

// Neue Funktion für den Markdown-Export
function exportMarkdown($data, $tabelle) {
    global $filename;
    ob_clean();
    header('Content-Type: text/markdown; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename . '.md');

    if (empty($data) || !is_array($data)) {
        echo "Keine Daten zum Export vorhanden.";
        return;
    }

    // Tabellenkopf
    $headers = array_keys($data[0]);
    $visibleHeaders = array_filter($headers, function($header) {
        return strcasecmp($header, 'id') !== 0;
    });

    // Markdown-Header
    // 1. Interpretiere HTML-Entities zurück zu UTF-8
    $headerRow = array_map(function($h) {
        return html_entity_decode($h, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }, $visibleHeaders);
    echo '| ' . implode(' | ', $headerRow) . " |\n";
    echo '| ' . implode(' | ', array_fill(0, count($visibleHeaders), '---')) . " |\n";

    // Datenzeilen
    foreach ($data as $row) {
        $rowValues = [];
        foreach ($visibleHeaders as $header) {
            $value = isset($row[$header]) ? $row[$header] : '';
            // Zeilenumbrüche und Pipes maskieren
            $value = str_replace(["\n", "|"], ["<br>", "\\|"], $value);
            // 2. Datum umwandeln wie im PDF-Export (yyyy-mm-dd zu dd.mm.yyyy)
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                $dateObj = DateTime::createFromFormat('Y-m-d', $value);
                if ($dateObj) {
                    $value = $dateObj->format('d.m.Y');
                }
            }
            // Interpretiere HTML-Entities zurück zu UTF-8
            $rowValues[] = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        echo '| ' . implode(' | ', $rowValues) . " |\n";
    }
}
