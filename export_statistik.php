<?php
ob_start(); // Start output buffering at the very beginning
require_once(__DIR__ . "/user_includes/all.head.php");
require_once(__DIR__ . "/inc/include.php");
require_once(__DIR__ . "/config/config.php");  // Changed from /config.php
require_once(__DIR__ . '/vendor/autoload.php');

// Clean any existing output
ob_clean();

// Hole Statistik-Auswahl
$selectedStat = isset($_POST['stat']) ? (int)$_POST['stat'] : 0;
if (!isset($statistik[$selectedStat])) {
    die('Ungültige Statistik-ID');
}

$stat = $statistik[$selectedStat];
$result = $db->query($stat['query']);
$data = isset($result['data']) ? $result['data'] : [];

// Sortiere die Daten
if (!empty($data)) {
    $valueKey = array_keys($data[0])[1];
    usort($data, function($a, $b) use ($valueKey) {
        return $b[$valueKey] <=> $a[$valueKey];
    });
}

// PDF erstellen
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8'); // 'P' für Portrait/Hochformat
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('LBSV Niedersachsen');
$pdf->SetTitle($stat['titel']);

// Kopf- und Fußzeile
$pdf->setHeaderFont(Array('helvetica', '', 10));
$pdf->setFooterFont(Array('helvetica', '', 8));
$pdf->SetHeaderData('', 0, TITEL, $stat['titel'] . "\nExportiert am: " . date('d.m.Y H:i'));

// Seitenränder verkleinern für mehr Platz
$pdf->SetMargins(10, 20, 10);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(TRUE, 15);

$pdf->AddPage();

// Große Überschrift
/*$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, $stat['titel'], 0, 1, 'C');
$pdf->Ln(5);*/

// Hole das Base64-Bild aus dem POST-Parameter und füge es ein
if (isset($_POST['chartImage'])) {
    $imgData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $_POST['chartImage']));
    
    if ($imgData) {  // Prüfen ob Dekodierung erfolgreich
        // Speichere temporär
        $tempFile = tempnam(sys_get_temp_dir(), 'chart');
        file_put_contents($tempFile, $imgData);
        
        // Berechne die Bildgröße (max 160mm breit für A4, proportionale Höhe)
        $imgSize = getimagesize($tempFile);
        if ($imgSize) {  // Prüfen ob es ein gültiges Bild ist
            $width = 160;
            $height = ($width / $imgSize[0]) * $imgSize[1];
            
            // Zentriere das Bild
            $x = ($pdf->getPageWidth() - $width) / 2;
            
            $pdf->Image($tempFile, $x, $pdf->GetY(), $width);
            unlink($tempFile);

            // Ausreichend Platz für das Bild + Abstand zur Tabelle
            $pdf->Ln($height + 5); // Reduziert auf 5mm Abstand nach dem Bild
        }
    }
}

// Füge die Tabelle unter dem Diagramm ein
$pdf->Ln(5); // Reduziert auf 5mm Abstand

// Tabelle
$pdf->SetFont('helvetica', '', 10);
if (!empty($data)) {
    $header = array_keys($data[0]);
    $w = array(140, 40); // Angepasste Spaltenbreiten für Hochformat

    // Header
    $pdf->SetFillColor(245, 245, 245);
    $pdf->Cell($w[0], 7, $header[0], 1, 0, 'L', true);
    $pdf->Cell($w[1], 7, $header[1], 1, 1, 'R', true);

    // Daten
    $pdf->SetFillColor(255, 255, 255);
    foreach($data as $row) {
        $pdf->Cell($w[0], 6, $row[$header[0]], 1, 0, 'L');
        $pdf->Cell($w[1], 6, $row[$header[1]], 1, 1, 'R');
    }
}

// Before PDF output
ob_end_clean();
$pdf->Output($stat['titel'] . '.pdf', 'D');
exit(); // Make sure nothing else is output
