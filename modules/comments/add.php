<?php
require_once '../../includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = getConnection();
    
    $subtask_id = (int)$_POST['subtask_id'];
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];
    $komentar = $conn->real_escape_string($_POST['komentar']);
    $link_attachment = !empty($_POST['link']) ? "'" . $conn->real_escape_string($_POST['link']) . "'" : "NULL";
    
    // Ambil info subtask dan task-nya
    $info = $conn->query("
        SELECT s.judul_sub, t.judul as task_judul, t.created_by as task_owner
        FROM subtasks s
        JOIN tasks t ON s.task_id = t.id
        WHERE s.id = $subtask_id
    ");
    $data = $info->fetch_assoc();
    $subtask_judul = $data['judul_sub'];
    $task_judul = $data['task_judul'];
    $task_owner = $data['task_owner'];
    
    $sql = "INSERT INTO comments (subtask_id, user_id, komentar, link_attachment) 
            VALUES ($subtask_id, $user_id, '$komentar', $link_attachment)";
    
    if ($conn->query($sql)) {
        $comment_id = $conn->insert_id;
        
        // Catat activity log
        logActivity($user_id, "Menambahkan komentar di subtask: $subtask_judul");
        
        // NOTIFIKASI 1: Untuk pembuat task (kalau bukan dirinya sendiri)
        if ($task_owner != $user_id) {
            $notif_owner = "💬 $username mengomentari tugas \"$subtask_judul\" di project \"$task_judul\"";
            notifyUser($task_owner, $notif_owner, 'info', base_url() . "/modules/dashboard.php#comment-$comment_id");
        }
        
        // NOTIFIKASI 2: Untuk semua anggota (informasi umum)
        $notif_all = "💬 $username memberikan komentar di project \"$task_judul\"";
        notifyAllMembers($notif_all, 'info', $user_id, base_url() . "/modules/dashboard.php#comment-$comment_id");
        
        // NOTIFIKASI 3: Jika ada link, kasih notifikasi khusus
        if (!empty($_POST['link'])) {
            $link_notif = "🔗 $username melampirkan link di komentar: " . $_POST['link'];
            notifyAllMembers($link_notif, 'success', $user_id, $_POST['link']);
        }
        
        $_SESSION['success'] = "Komentar berhasil ditambahkan!";
    } else {
        $_SESSION['error'] = "Gagal menambahkan komentar: " . $conn->error;
    }
    
    $conn->close();
}

// Redirect kembali ke halaman sebelumnya
header('Location: ' . $_SERVER['HTTP_REFERER']);
exit();
?>