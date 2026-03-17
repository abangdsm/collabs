<?php
require_once '../includes/functions.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
    exit();
}

$conn = getConnection();

$task_id = (int)$_POST['task_id'];
$order = json_decode($_POST['order'], true); // Array of subtask IDs in new order

if (!$order || !is_array($order)) {
    echo json_encode(['success' => false, 'message' => 'Data order tidak valid']);
    $conn->close();
    exit();
}

// Mulai transaction
$conn->begin_transaction();

try {
    // Update urutan setiap subtask
    foreach ($order as $index => $subtask_id) {
        $urutan = $index + 1; // Mulai dari 1
        $subtask_id = (int)$subtask_id;
        
        $sql = "UPDATE subtasks SET urutan = $urutan WHERE id = $subtask_id AND task_id = $task_id";
        if (!$conn->query($sql)) {
            throw new Exception('Gagal update urutan: ' . $conn->error);
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    // Catat activity log
    logActivity($_SESSION['user_id'], "Mengubah urutan subtasks di task ID: $task_id");
    
    echo json_encode(['success' => true, 'message' => 'Urutan berhasil disimpan']);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>