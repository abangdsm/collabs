<?php
require_once '../includes/functions.php';
requireLogin();

header('Content-Type: application/json');

// Aktifkan error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log request yang masuk
error_log("Delete task request received: " . print_r($_POST, true));

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Cek apakah task_id ada
    if (!isset($_POST['task_id'])) {
        echo json_encode([
            'success' => false, 
            'message' => 'Task ID tidak ditemukan'
        ]);
        exit();
    }
    
    $conn = getConnection();
    
    // Cek koneksi database
    if ($conn->connect_error) {
        echo json_encode([
            'success' => false, 
            'message' => 'Koneksi database gagal: ' . $conn->connect_error
        ]);
        exit();
    }
    
    $task_id = (int)$_POST['task_id'];
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];
    
    // Validasi task_id
    if ($task_id <= 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'Task ID tidak valid'
        ]);
        $conn->close();
        exit();
    }
    
    // Cek apakah task dengan ID tersebut ada
    $check = $conn->query("SELECT id, created_by FROM tasks WHERE id = $task_id");
    
    if (!$check) {
        echo json_encode([
            'success' => false, 
            'message' => 'Error saat mengecek task: ' . $conn->error
        ]);
        $conn->close();
        exit();
    }
    
    if ($check->num_rows == 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'Task tidak ditemukan'
        ]);
        $conn->close();
        exit();
    }
    
    $task = $check->fetch_assoc();
    
    // Cek apakah user berhak hapus (admin atau pembuat)
    if ($role == 'admin' || $task['created_by'] == $user_id) {
        
        // Mulai transaction untuk keamanan
        $conn->begin_transaction();
        
        try {
            // Hapus subtasks terkait (manual karena ON DELETE CASCADE mungkin tidak bekerja)
            $delete_subtasks = $conn->query("DELETE FROM subtasks WHERE task_id = $task_id");
            
            if (!$delete_subtasks) {
                throw new Exception('Gagal menghapus subtasks: ' . $conn->error);
            }
            
            // Hapus task
            $sql = "DELETE FROM tasks WHERE id = $task_id";
            $result = $conn->query($sql);
            
            if (!$result) {
                throw new Exception('Gagal menghapus task: ' . $conn->error);
            }
            
            // Commit transaction
            $conn->commit();
            
            // Catat activity log
            logActivity($user_id, "Menghapus task ID: $task_id");
            
            echo json_encode([
                'success' => true, 
                'message' => 'Tugas berhasil dihapus'
            ]);
            
        } catch (Exception $e) {
            // Rollback jika ada error
            $conn->rollback();
            
            echo json_encode([
                'success' => false, 
                'message' => $e->getMessage()
            ]);
        }
        
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Anda tidak berhak menghapus tugas ini'
        ]);
    }
    
    $conn->close();
    
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Method tidak diizinkan. Gunakan POST.'
    ]);
}
?>