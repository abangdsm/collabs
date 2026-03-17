<?php
require_once '../includes/functions.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
    exit();
}

if (!isset($_POST['comment_id'])) {
    echo json_encode(['success' => false, 'message' => 'Comment ID tidak ditemukan']);
    exit();
}

$conn = getConnection();
$comment_id = (int)$_POST['comment_id'];
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Cek kepemilikan komentar
$result = $conn->query("SELECT user_id, subtask_id FROM comments WHERE id = $comment_id");
if ($result->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Komentar tidak ditemukan']);
    $conn->close();
    exit();
}

$comment = $result->fetch_assoc();

// CEK AKSES - Admin boleh hapus semua, member hanya boleh hapus komentar sendiri
if ($role != 'admin' && $comment['user_id'] != $user_id) {
    echo json_encode(['success' => false, 'message' => 'Anda tidak berhak menghapus komentar ini!']);
    $conn->close();
    exit();
}

// Hapus komentar (dan semua balasannya karena ON DELETE CASCADE)
$conn->query("DELETE FROM comments WHERE id = $comment_id");

if ($conn->affected_rows > 0) {
    logActivity($user_id, "Menghapus komentar ID: $comment_id");
    echo json_encode(['success' => true, 'message' => 'Komentar berhasil dihapus']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus komentar']);
}

$conn->close();
?>