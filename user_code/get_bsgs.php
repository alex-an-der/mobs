<?php
// We only need the database connection for this simple AJAX endpoint
require_once(__DIR__."/../config/db_connect.php");

header('Content-Type: application/json');

// Validate and sanitize input
$verband_id = isset($_POST['verband_id']) ? (int)$_POST['verband_id'] : 0;

if ($verband_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid verband ID',
        'bsgs' => []
    ]);
    exit;
}

try {
    // Query for BSGs belonging to the selected verband
    $query = "SELECT id, BSG FROM b_bsg WHERE Verband = ? ORDER BY BSG";
    $args = [$verband_id];
    
    // Use the database class to execute the query safely
    $result = $db->query($query, $args);
    
    if (isset($result['data']) && !empty($result['data'])) {
        echo json_encode([
            'success' => true,
            'bsgs' => $result['data']
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'No BSGs found for this Verband',
            'bsgs' => []
        ]);
    }
} catch (Exception $e) {
    // Log the error but don't expose details to client
    error_log("Error fetching BSGs: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching BSGs',
        'bsgs' => []
    ]);
}
?>
