<?php
require_once '../includes/functions.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['task_id'])) {
    $conn = getConnection();
    $task_id = (int)$_POST['task_id'];
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];
    
    // Cek apakah user berhak hapus (admin atau pembuat)
    $check = $conn->query("SELECT created_by FROM tasks WHERE id = $task_id");
    $task = $check->fetch_assoc();
    
    if ($role == 'admin' || $task['created_by'] == $user_id) {
        // Hapus task (subtasks akan kehapus otomatis karena ON DELETE CASCADE)
        $sql = "DELETE FROM tasks WHERE id = $task_id";
        
        if ($conn->query($sql)) {
            // Catat activity log
            logActivity($user_id, "Menghapus task ID: $task_id");
            
            echo json_encode(['success' => true, 'message' => 'Tugas berhasil dihapus']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus: ' . $conn->error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Anda tidak berhak menghapus tugas ini']);
    }
    
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>