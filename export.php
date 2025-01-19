<?php
require_once(__DIR__ . "/mods/all.head.php");
require_once(__DIR__ . "/inc/include.php");

// Validate input
$format = $_POST['format'] ?? '';
$tabelle = $_POST['tabelle'] ?? '';
$tabid = $_POST['tabid'] ?? '';
$exportAll = ($_POST['exportAll'] ?? '1') === '1';
$ids = $_POST['ids'] ?? '';

if (!$tabelle || !isset($anzuzeigendeDaten[$tabid])) {
    die('Invalid parameters');
}

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

// Export based on format
switch ($format) {
    case 'pdf':
        require_once(__DIR__ . '/vendor/autoload.php'); // Needs TCPDF
        exportPDF($data, $tabelle);
        break;
    case 'csv':
        exportCSV($data, $tabelle);
        break;
    case 'excel':
        require_once(__DIR__ . '/vendor/autoload.php'); // Needs PhpSpreadsheet
        exportExcel($data, $tabelle);
        break;
    default:
        die('Invalid format');
}

function exportPDF($data, $tabelle) {
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetTitle($tabelle);
    $pdf->AddPage('L');
    
    // Add table
    $headers = array_keys($data[0]);
    $rows = array_map(function($row) {
        return array_values($row);
    }, $data);
    
    $pdf->SetFont('helvetica', '', 8);
    $pdf->Table($headers, $rows);
    
    $pdf->Output($tabelle . '.pdf', 'D');
}

function exportCSV($data, $tabelle) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $tabelle . '.csv');
    
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

function exportExcel($data, $tabelle) {
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Headers
    $headers = array_keys($data[0]);
    $col = 1;
    foreach ($headers as $header) {
        $sheet->setCellValueByColumnAndRow($col++, 1, $header);
    }
    
    // Data
    $row = 2;
    foreach ($data as $rowData) {
        $col = 1;
        foreach ($rowData as $value) {
            $sheet->setCellValueByColumnAndRow($col++, $row, $value);
        }
        $row++;
    }
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $tabelle . '.xlsx"');
    header('Cache-Control: max-age=0');
    
    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save('php://output');
}
