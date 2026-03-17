<?php
require_once '../includes/functions.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
    exit();
}

if (!isset($_POST['subtask_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Subtask ID tidak ditemukan']);
    exit();
}

$conn = getConnection();
$subtask_id = (int)$_POST['subtask_id'];
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Cek kepemilikan
$stmt = $conn->prepare("SELECT created_by FROM subtasks WHERE id = ?");
$stmt->bind_param("i", $subtask_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Subtask tidak ditemukan']);
    $stmt->close();
    $conn->close();
    exit();
}

$subtask = $result->fetch_assoc();
$stmt->close();

// Cek hak akses
if ($role != 'admin' && $subtask['created_by'] != $user_id) {
    echo json_encode(['success' => false, 'message' => 'Anda tidak berhak menghapus subtask ini']);
    $conn->close();
    exit();
}

// Hapus subtask
$stmt = $conn->prepare("DELETE FROM subtasks WHERE id = ?");
$stmt->bind_param("i", $subtask_id);

if ($stmt->execute()) {
    logActivity($user_id, "Menghapus subtask ID: $subtask_id");
    echo json_encode(['success' => true, 'message' => 'Subtask berhasil dihapus']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>