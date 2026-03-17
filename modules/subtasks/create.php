<?php
require_once '../../includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = getConnection();
    
    $task_id = (int)$_POST['task_id'];
    $judul_sub = $conn->real_escape_string($_POST['judul_sub']);
    $deskripsi = $conn->real_escape_string($_POST['deskripsi'] ?? '');
    $priority = $conn->real_escape_string($_POST['priority'] ?? 'medium');
    $deadline = !empty($_POST['deadline']) ? "'" . $conn->real_escape_string($_POST['deadline']) . "'" : "NULL";
    $status = $conn->real_escape_string($_POST['status'] ?? 'proses');
    $created_by = $_SESSION['user_id'];
    
    // Cek urutan terakhir untuk task ini
    $result = $conn->query("SELECT MAX(urutan) as max_urutan FROM subtasks WHERE task_id = $task_id");
    $row = $result->fetch_assoc();
    $urutan = ($row['max_urutan'] ?? 0) + 1;
    
    $sql = "INSERT INTO subtasks (task_id, judul_sub, deskripsi, priority, deadline, status, created_by, urutan) 
            VALUES ($task_id, '$judul_sub', '$deskripsi', '$priority', $deadline, '$status', $created_by, $urutan)";
    
    if ($conn->query($sql)) {
        $subtask_id = $conn->insert_id;
        
        // Catat activity log
        logActivity($created_by, "Menambahkan subtask ID: $subtask_id ke task ID: $task_id");
        
        // Buat notifikasi untuk anggota tim (kecuali dirinya sendiri)
        $notif_message = "$_SESSION[username] menambahkan tugas: $judul_sub";
        $conn->query("INSERT INTO notifications (user_id, message, type) 
                     SELECT id, '$notif_message', 'info' FROM users WHERE id != $created_by");
        
        $_SESSION['success'] = "Daftar tugas berhasil ditambahkan!";
    } else {
        $_SESSION['error'] = "Gagal menambahkan: " . $conn->error;
    }
    
    $conn->close();
}

header('Location: ' . base_url() . '/modules/dashboard.php');
exit();
?>