<?php
require_once '../includes/functions.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
    exit();
}

if (!isset($_POST['comment_id']) || !isset($_POST['komentar'])) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    exit();
}

$conn = getConnection();
$comment_id = (int)$_POST['comment_id'];
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$komentar = $conn->real_escape_string(trim($_POST['komentar']));

if (empty($komentar)) {
    echo json_encode(['success' => false, 'message' => 'Komentar tidak boleh kosong']);
    $conn->close();
    exit();
}

// Cek kepemilikan komentar
$result = $conn->query("SELECT user_id, subtask_id FROM comments WHERE id = $comment_id");
if ($result->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Komentar tidak ditemukan']);
    $conn->close();
    exit();
}

$comment = $result->fetch_assoc();

// CEK AKSES - Admin boleh edit semua, member hanya boleh edit komentar sendiri
if ($role != 'admin' && $comment['user_id'] != $user_id) {
    echo json_encode(['success' => false, 'message' => 'Anda tidak berhak mengedit komentar ini!']);
    $conn->close();
    exit();
}

// Update komentar
$conn->query("UPDATE comments SET komentar = '$komentar' WHERE id = $comment_id");

if ($conn->affected_rows >= 0) {
    logActivity($user_id, "Mengupdate komentar ID: $comment_id");
    echo json_encode(['success' => true, 'message' => 'Komentar berhasil diupdate']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal mengupdate komentar']);
}

$conn->close();
?>