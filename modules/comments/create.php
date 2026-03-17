<?php
require_once '../../includes/functions.php';
requireLogin();

header('Content-Type: application/json');

// Aktifkan error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
    exit();
}

// Log data yang diterima
error_log('POST data: ' . print_r($_POST, true));

$conn = getConnection();

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal: ' . $conn->connect_error]);
    exit();
}

// Validasi input
if (empty($_POST['subtask_id']) || empty($_POST['komentar'])) {
    echo json_encode(['success' => false, 'message' => 'Subtask ID dan komentar harus diisi']);
    $conn->close();
    exit();
}

$subtask_id = (int)$_POST['subtask_id'];
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$komentar = $conn->real_escape_string($_POST['komentar']);
$link = !empty($_POST['link']) ? "'" . $conn->real_escape_string($_POST['link']) . "'" : "NULL";

// Ambil info subtask untuk notifikasi
$info = $conn->query("
    SELECT s.judul_sub, t.judul as task_judul, t.created_by as task_owner
    FROM subtasks s
    JOIN tasks t ON s.task_id = t.id
    WHERE s.id = $subtask_id
");

if (!$info) {
    echo json_encode(['success' => false, 'message' => 'Error query: ' . $conn->error]);
    $conn->close();
    exit();
}

$data = $info->fetch_assoc();
if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Subtask tidak ditemukan']);
    $conn->close();
    exit();
}

$subtask_judul = $data['judul_sub'];
$task_judul = $data['task_judul'];
$task_owner = $data['task_owner'];

$sql = "INSERT INTO comments (subtask_id, user_id, komentar, link_attachment) 
        VALUES ($subtask_id, $user_id, '$komentar', $link)";

error_log('SQL: ' . $sql);

if ($conn->query($sql)) {
    $comment_id = $conn->insert_id;
    
    // Catat activity log
    logActivity($user_id, "Menambahkan komentar di subtask: $subtask_judul");
    
    // Notifikasi untuk pembuat task
    if ($task_owner != $user_id) {
        $notif_owner = "$username mengomentari tugas \"$subtask_judul\" di project \"$task_judul\"";
        notifyUser($task_owner, $notif_owner, 'info', base_url() . "/modules/dashboard.php#comment-$comment_id");
    }
    
    // Notifikasi untuk semua anggota
    $notif_all = "$username memberikan komentar di project \"$task_judul\"";
    notifyAllMembers($notif_all, 'info', $user_id, base_url() . "/modules/dashboard.php#comment-$comment_id");
    
    echo json_encode([
        'success' => true, 
        'message' => 'Komentar berhasil ditambahkan',
        'comment_id' => $comment_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menambahkan komentar: ' . $conn->error]);
}

$conn->close();
?>