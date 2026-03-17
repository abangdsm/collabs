<?php
require_once dirname(__DIR__) . '/includes/functions.php';
requireLogin();

header('Content-Type: application/json');

// Handle preflight request untuk CORS (jika diperlukan)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Pastikan method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false, 
        'message' => 'Method tidak diizinkan. Gunakan POST.'
    ]);
    exit();
}

// Pastikan task_id ada
if (!isset($_POST['task_id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'Task ID tidak ditemukan'
    ]);
    exit();
}

$conn = getConnection();

// Cek koneksi database
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Koneksi database gagal'
    ]);
    exit();
}

$task_id = (int)$_POST['task_id'];
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Validasi task_id
if ($task_id <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'Task ID tidak valid'
    ]);
    $conn->close();
    exit();
}

// Cek apakah task ada dan ambil data pembuat
$stmt = $conn->prepare("SELECT id, created_by FROM tasks WHERE id = ?");
$stmt->bind_param("i", $task_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode([
        'success' => false, 
        'message' => 'Task tidak ditemukan'
    ]);
    $stmt->close();
    $conn->close();
    exit();
}

$task = $result->fetch_assoc();
$stmt->close();

// Cek hak akses (admin atau pembuat)
if ($role !== 'admin' && $task['created_by'] != $user_id) {
    http_response_code(403);
    echo json_encode([
        'success' => false, 
        'message' => 'Anda tidak berhak menghapus tugas ini'
    ]);
    $conn->close();
    exit();
}

// Mulai transaction
$conn->begin_transaction();

try {
    // Hapus task (subtasks akan kehapus otomatis karena ON DELETE CASCADE)
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->bind_param("i", $task_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Gagal menghapus task: ' . $stmt->error);
    }
    
    // Cek apakah benar-benar terhapus
    if ($stmt->affected_rows === 0) {
        throw new Exception('Tidak ada data yang dihapus');
    }
    
    $stmt->close();
    
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
    
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>