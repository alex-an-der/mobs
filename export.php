<?php
// Aktiviere Output Buffering am Anfang der Datei
ob_start();
require_once(__DIR__ . "/mods/all.head.php");
require_once(__DIR__ . "/inc/include.php");
// Fehlerausgabe aktivieren
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Validate input
$format = $_POST['format'] ?? '';
$tabelle = $_POST['tabelle'] ?? '';
$tabid = $_POST['tabid'] ?? '';
$exportAll = ($_POST['exportAll'] ?? '1') === '1';
$ids = $_POST['ids'] ?? '';

if (!$tabelle || !isset($anzuzeigendeDaten[$tabid])) {
    die('Invalid parameters');
}

// Bestimme den Anzeigenamen für die Datei
$displayName = $anzuzeigendeDaten[$tabid]['auswahltext'] ?? $tabelle;
$filename = str_replace(' ', '_', $displayName);

// Get data
$query = $anzuzeigendeDaten[$tabid]['query'];
if (!$exportAll && !empty($ids)) {
    $idArray = explode(',', $ids);
    $idList = implode(',', array_map('intval', $idArray));
    $query = preg_replace('/WHERE/i', "WHERE id IN ($idList) AND ", $query, 1);
    if (stripos($query, 'WHERE') === false) {
        $query .= " WHERE id IN ($idList)";
    }
}

$result = $db->query($query);
if (!isset($result['data'])) {
    die('No data available');
}
$data = $result['data'];

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
foreach ($data as &$row) {
    foreach ($row as $key => &$value) {
        if (isset($FKdata[$key]) && isset($FKdata[$key][$value])) {
            $value = $FKdata[$key][$value];
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
$data = sortData($data, $sortColumn, $sortOrder);

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
    default:
        die('Invalid format');
}

// Modifiziere die PDF-Export-Funktion für bessere Fehlerbehandlung
function exportPDF($data, $tabelle) {
    global $filename;
    try {
        ob_clean();
        
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetTitle($filename);
        $pdf->AddPage('L');
        $pdf->SetFont('helvetica', '', 8);
        
        // Berechne verfügbare Seitenbreite (in mm)
        $pageWidth = $pdf->getPageWidth();
        $margins = $pdf->getMargins();
        $availableWidth = $pageWidth - $margins['left'] - $margins['right'];
        
        // Headers mit mehr Padding und Styling
        $headers = array_keys($data[0]);
        $visibleHeaders = array_filter($headers, function($header) {
            return strcasecmp($header, 'id') !== 0;
        });
        
        // Berechne die optimale Spaltenbreite
        $columnWidths = [];
        $totalContentWidth = 0;
        foreach ($visibleHeaders as $header) {
            $maxWidth = strlen($header) * 2; // Startbreite basierend auf Header
            
            // Finde längsten Inhalt
            foreach ($data as $row) {
                $len = strlen($row[$header]) * 2;
                $maxWidth = max($maxWidth, $len);
            }
            
            $columnWidths[$header] = $maxWidth;
            $totalContentWidth += $maxWidth;
        }
        
        // Skaliere Spaltenbreiten wenn nötig
        if ($totalContentWidth > $availableWidth) {
            $scale = $availableWidth / $totalContentWidth;
            foreach ($columnWidths as &$width) {
                $width *= $scale;
            }
        }
        
        // Erstelle die Tabelle
        $html = '<table border="1" cellpadding="5" cellspacing="0">';
        
        // Header-Zeile
        $html .= '<tr style="background-color:#f5f5f5;">';
        foreach ($visibleHeaders as $header) {
            $html .= sprintf(
                '<th style="font-weight:bold; text-align:left; width:%.1fmm; white-space:nowrap;">%s</th>',
                $columnWidths[$header],
                htmlspecialchars($header)
            );
        }
        $html .= '</tr>';
        
        // Datenzeilen
        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($row as $key => $value) {
                if (strcasecmp($key, 'id') !== 0) {
                    $html .= sprintf(
                        '<td style="width:%.1fmm; white-space:nowrap;">%s</td>',
                        $columnWidths[$key],
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
        // Debug: Prüfe Daten
        if (empty($data)) {
            die('Keine Daten zum Exportieren vorhanden');
        }

        // Erstelle neue Instanz
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Debug: Log Datenstruktur
        error_log('Export data structure: ' . print_r($data, true));
        
        // Headers (ohne ID) sammeln
        $headers = array_filter(array_keys($data[0]), function($header) {
            return strcasecmp($header, 'id') !== 0;
        });
        $headers = array_values($headers); // Array neu indizieren
        
        // Debug: Log Headers
        error_log('Export headers: ' . print_r($headers, true));
        
        // Headers schreiben
        foreach ($headers as $colIndex => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter . '1', $header);
            
            // Header-Styling
            $sheet->getStyle($colLetter . '1')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 11
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F5F5F5']
                ]
            ]);
        }
        
        // Daten schreiben
        $rowIndex = 2;
        foreach ($data as $rowData) {
            $colIndex = 1;
            foreach ($headers as $header) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                $value = $rowData[$header] ?? '';
                
                // Debug: Log jede Zelle
                error_log("Setting cell {$colLetter}{$rowIndex} to: " . print_r($value, true));
                
                // Setze Zellwert
                $sheet->setCellValue($colLetter . $rowIndex, $value);
                
                // Überprüfe, ob der Wert geschrieben wurde
                $actualValue = $sheet->getCell($colLetter . $rowIndex)->getValue();
                error_log("Actual cell value {$colLetter}{$rowIndex}: " . print_r($actualValue, true));
                
                $colIndex++;
            }
            $rowIndex++;
        }
        
        // Auto-size columns
        foreach ($headers as $colIndex => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
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
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename . '.csv');
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
    
    // Headers
    fputcsv($output, array_keys($data[0]), ';');
    
    // Data
    foreach ($data as $row) {
        fputcsv($output, $row, ';');
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
