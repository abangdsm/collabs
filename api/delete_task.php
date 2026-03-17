<?php
require_once '../includes/functions.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
    exit();
}

if (!isset($_POST['task_id'])) {
    echo json_encode(['success' => false, 'message' => 'Task ID tidak ditemukan']);
    exit();
}

$conn = getConnection();
$task_id = (int)$_POST['task_id'];
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Cek kepemilikan task
$result = $conn->query("SELECT created_by FROM tasks WHERE id = $task_id");
if ($result->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Task tidak ditemukan']);
    $conn->close();
    exit();
}

$task = $result->fetch_assoc();

// CEK AKSES - Admin boleh hapus semua, member hanya boleh hapus tugas sendiri
if ($role != 'admin' && $task['created_by'] != $user_id) {
    echo json_encode(['success' => false, 'message' => 'Anda tidak berhak menghapus tugas ini!']);
    $conn->close();
    exit();
}

// Hapus task
$conn->begin_transaction();

try {
    // Hapus subtasks terkait
    $conn->query("DELETE FROM subtasks WHERE task_id = $task_id");
    
    // Hapus task
    $conn->query("DELETE FROM tasks WHERE id = $task_id");
    
    $conn->commit();
    
    logActivity($user_id, "Menghapus task ID: $task_id");
    
    echo json_encode(['success' => true, 'message' => 'Tugas berhasil dihapus']);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus: ' . $e->getMessage()]);
}

$conn->close();
?>