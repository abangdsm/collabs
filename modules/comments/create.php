<?php
require_once '../../includes/functions.php';
requireLogin();

header('Content-Type: application/json');

// Aktifkan error reporting tapi tangkap ke log, jangan ditampilkan
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

try {
    // Validasi method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method tidak diizinkan');
    }

    // Validasi input required
    if (empty($_POST['subtask_id']) || empty($_POST['komentar'])) {
        throw new Exception('Subtask ID dan komentar harus diisi');
    }

    $conn = getConnection();

    if ($conn->connect_error) {
        throw new Exception('Koneksi database gagal: ' . $conn->connect_error);
    }

    // Sanitasi input
    $subtask_id = (int)$_POST['subtask_id'];
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];
    $komentar = $conn->real_escape_string(trim($_POST['komentar']));
    $link = !empty($_POST['link']) ? "'" . $conn->real_escape_string(trim($_POST['link'])) . "'" : "NULL";
    $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : "NULL";

    // Ambil info subtask untuk notifikasi
    $info = $conn->query("
        SELECT s.judul_sub, t.judul as task_judul, t.created_by as task_owner
        FROM subtasks s
        JOIN tasks t ON s.task_id = t.id
        WHERE s.id = $subtask_id
    ");

    if (!$info) {
        throw new Exception('Error query: ' . $conn->error);
    }

    if ($info->num_rows == 0) {
        throw new Exception('Subtask tidak ditemukan');
    }

    $data = $info->fetch_assoc();
    $subtask_judul = $data['judul_sub'];
    $task_judul = $data['task_judul'];
    $task_owner = $data['task_owner'];

    // Insert komentar
    $sql = "INSERT INTO comments (subtask_id, user_id, komentar, link_attachment, parent_id) 
            VALUES ($subtask_id, $user_id, '$komentar', $link, $parent_id)";

    if (!$conn->query($sql)) {
        throw new Exception('Gagal menambahkan komentar: ' . $conn->error);
    }

    $comment_id = $conn->insert_id;
    
    // Catat activity log
    logActivity($user_id, "Menambahkan " . ($parent_id != "NULL" ? "balasan" : "komentar") . " di subtask: $subtask_judul");
    
    $notif_message = $parent_id != "NULL" 
    ? "$username membalas komentar di tugas \"$subtask_judul\""
    : "$username berkomentar di tugas \"$subtask_judul\"";
    
    // Notifikasi untuk pembuat task (jika bukan dirinya sendiri)
    if ($task_owner != $user_id) {
        notifyUser($task_owner, $notif_message, 'info', base_url() . "/modules/dashboard.php");
    }
    
    // Notifikasi untuk semua anggota (kecuali dirinya sendiri)
    notifyAllMembers($notif_message, 'info', $user_id, base_url() . "/modules/dashboard.php");
    
    // Response sukses
    echo json_encode([
        'success' => true,
        'message' => 'Komentar berhasil ditambahkan',
        'comment_id' => $comment_id,
        'is_reply' => ($parent_id != "NULL")
    ]);

} catch (Exception $e) {
    // Log error ke file
    error_log('Error in comments/create.php: ' . $e->getMessage());
    error_log('POST data: ' . print_r($_POST, true));
    
    // Kirim response JSON dengan pesan error
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// Tutup koneksi jika ada
if (isset($conn) && $conn) {
    $conn->close();
}
?>