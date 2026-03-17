<?php
require_once '../includes/functions.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
    exit();
}

if (!isset($_POST['subtask_id'])) {
    echo json_encode(['success' => false, 'message' => 'Subtask ID tidak ditemukan']);
    exit();
}

$conn = getConnection();
$subtask_id = (int)$_POST['subtask_id'];
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Cek kepemilikan subtask
$result = $conn->query("SELECT created_by FROM subtasks WHERE id = $subtask_id");
if ($result->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Subtask tidak ditemukan']);
    $conn->close();
    exit();
}

$subtask = $result->fetch_assoc();

// CEK AKSES - Admin boleh hapus semua, member hanya boleh hapus subtask sendiri
if ($role != 'admin' && $subtask['created_by'] != $user_id) {
    echo json_encode(['success' => false, 'message' => 'Anda tidak berhak menghapus subtask ini!']);
    $conn->close();
    exit();
}

// Hapus subtask
$conn->query("DELETE FROM subtasks WHERE id = $subtask_id");

if ($conn->affected_rows > 0) {
    logActivity($user_id, "Menghapus subtask ID: $subtask_id");
    echo json_encode(['success' => true, 'message' => 'Subtask berhasil dihapus']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus subtask']);
}

$conn->close();
?>