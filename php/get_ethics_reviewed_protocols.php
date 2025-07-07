<?php
require_once '../database/config.php';
header('Content-Type: application/json');
try {
    $db = getDB();
    $rows = $db->query("SELECT protocol_number, title, department, status, action_taken FROM ethics_reviewed_protocols")->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rows);
} catch (Exception $e) {
    echo json_encode([]);
} 