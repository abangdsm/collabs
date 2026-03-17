<?php
require_once '../includes/functions.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
    exit();
}

$conn = getConnection();
$user_id = $_SESSION['user_id'];

if (isset($_POST['all']) && $_POST['all'] == 'true') {
    // Tandai semua sebagai sudah dibaca
    $conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $user_id");
    echo json_encode(['success' => true, 'message' => 'Semua notifikasi ditandai sudah dibaca']);
} 
elseif (isset($_POST['id'])) {
    // Tandai satu notifikasi
    $id = (int)$_POST['id'];
    $conn->query("UPDATE notifications SET is_read = 1 WHERE id = $id AND user_id = $user_id");
    echo json_encode(['success' => true, 'message' => 'Notifikasi ditandai sudah dibaca']);
}
else {
    echo json_encode(['success' => false, 'message' => 'Parameter tidak lengkap']);
}

$conn->close();
?>