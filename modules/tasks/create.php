<?php
require_once '../../includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = getConnection();
    
    $judul = $conn->real_escape_string($_POST['judul']);
    $created_by = $_SESSION['user_id'];
    $username = $_SESSION['username'];
    
    $sql = "INSERT INTO tasks (judul, created_by) VALUES ('$judul', $created_by)";
    
    if ($conn->query($sql)) {
        $task_id = $conn->insert_id;
        
        // Catat activity log
        logActivity($created_by, "Menambahkan task ID: $task_id - $judul");
        
        // NOTIFIKASI: Beri tahu semua anggota (kecuali dirinya sendiri)
        $notif_message = "📋 $username menambahkan project baru: \"$judul\"";
        notifyAllMembers($notif_message, 'info', $created_by, base_url() . "/modules/dashboard.php#task-$task_id");
        
        $_SESSION['success'] = "Judul tugas berhasil ditambahkan!";
    } else {
        $_SESSION['error'] = "Gagal menambahkan judul tugas: " . $conn->error;
    }
    
    $conn->close();
}

header('Location: ' . base_url() . '/modules/dashboard.php');
exit();
?>