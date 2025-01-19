<?php
session_start();
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['column']) && isset($data['order'])) {
    $_SESSION['sortColumn'] = $data['column'];
    $_SESSION['sortOrder'] = $data['order'];
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error']);
}
